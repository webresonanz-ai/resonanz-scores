<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Config;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Score;
use App\Models\User;
use RuntimeException;

final class ScoreController
{
    private Score $scores;
    private User $users;
    private string $pdfStoragePath;
    private string $imageStoragePath;

    public function __construct(Database $database)
    {
        $this->scores = new Score($database);
        $this->users = new User($database);
        $this->pdfStoragePath = BASE_PATH . '/stored/pdf';
        $this->imageStoragePath = BASE_PATH . '/stored/images';
    }

    public function index(Request $request): never
    {
        Response::json([
            'data' => $this->transformScores($this->scores->allApproved(), $request),
        ]);
    }

    public function show(Request $request): never
    {
        $scoreId = (int) ($request->query['id'] ?? 0);

        if ($scoreId <= 0) {
            Response::json([
                'message' => 'Score not found.',
            ], 404);
        }

        $score = $this->scores->findApproved($scoreId);

        if ($score === null) {
            Response::json([
                'message' => 'Score not found.',
            ], 404);
        }

        Response::json([
            'data' => $this->transformScore($score, $request),
        ]);
    }

    public function mine(Request $request): never
    {
        $auth = $request->attribute('auth', []);
        $userId = (int) ($auth['sub'] ?? 0);

        $this->ensureComposer($request);

        Response::json([
            'data' => $this->transformScores($this->scores->byComposerId($userId), $request),
        ]);
    }

    public function store(Request $request): never
    {
        $auth = $request->attribute('auth', []);
        $userId = (int) ($auth['sub'] ?? 0);

        $this->ensureComposer($request);

        $title = trim((string) $request->input('title', ''));
        $composer = trim((string) $request->input('composer', ''));
        $genre = trim((string) $request->input('genre', ''));
        $difficulty = trim((string) $request->input('difficulty', ''));
        $description = trim((string) $request->input('description', ''));
        $price = (float) $request->input('price', 0);
        $isArranged = filter_var($request->input('is_arranged', false), FILTER_VALIDATE_BOOLEAN);
        $arranger = trim((string) $request->input('arranger', ''));
        $pdfFile = $request->file('upload_pdf');

        if ($title === '' || $composer === '' || $genre === '' || $difficulty === '' || $description === '') {
            Response::json([
                'message' => 'Please complete all required composition fields.',
            ], 422);
        }

        if ($price < 0) {
            Response::json([
                'message' => 'Price must be zero or higher.',
            ], 422);
        }

        if ($isArranged && $arranger === '') {
            Response::json([
                'message' => 'Arranger is required when the score is marked as arranged.',
            ], 422);
        }

        if ($pdfFile === null) {
            Response::json([
                'message' => 'Please upload a PDF file for the composition.',
            ], 422);
        }

        try {
            $storedPdf = $this->storeUploadedFile($pdfFile, $this->pdfStoragePath, ['pdf']);
            $pages = $this->countPdfPages($storedPdf['absolute_path']);
            $imageFile = $request->file('image');
            $storedImage = $imageFile !== null
                ? $this->storeUploadedFile($imageFile, $this->imageStoragePath, ['jpg', 'jpeg', 'png', 'webp'])
                : Score::DEFAULT_IMAGE;

            $created = $this->scores->create([
                'user_id' => $userId,
                'title' => $title,
                'composer' => $composer,
                'arranger' => $isArranged ? $arranger : null,
                'is_arranged' => $isArranged ? 1 : 0,
                'genre' => $genre,
                'difficulty' => $difficulty,
                'price' => number_format($price, 2, '.', ''),
                'image' => is_array($storedImage) ? $storedImage['relative_path'] : $storedImage,
                'pdf_path' => $storedPdf['relative_path'],
                'description' => $description,
                'pages' => $pages,
            ]);
        } catch (RuntimeException $exception) {
            Response::json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        Response::json([
            'message' => 'Composition submitted for approval. You will receive an email once it is reviewed.',
            'data' => $this->transformScore($created, $request),
        ], 201);
    }

    public function image(Request $request): never
    {
        $relativePath = trim((string) ($request->query['path'] ?? ''));
        $normalizedPath = str_replace('\\', '/', $relativePath);

        if ($normalizedPath === '' || !str_starts_with($normalizedPath, 'stored/images/')) {
            Response::json([
                'message' => 'Image not found.',
            ], 404);
        }

        $absolutePath = BASE_PATH . '/' . ltrim($normalizedPath, '/');

        if (!is_file($absolutePath)) {
            Response::json([
                'message' => 'Image not found.',
            ], 404);
        }

        $mimeType = mime_content_type($absolutePath) ?: 'application/octet-stream';
        Response::file($absolutePath, $mimeType);
    }

    public function pdfPreview(Request $request): never
    {
        if (!$this->isPreviewRequest($request)) {
            Response::json([
                'message' => 'PDF preview is not available for direct download.',
            ], 403);
        }

        $scoreId = (int) ($request->query['id'] ?? 0);

        if ($scoreId <= 0) {
            Response::json([
                'message' => 'Score not found.',
            ], 404);
        }

        $score = $this->scores->findApproved($scoreId);

        if ($score === null) {
            Response::json([
                'message' => 'Score not found.',
            ], 404);
        }

        $absolutePath = $this->resolvePdfAbsolutePath((string) ($score['pdf_path'] ?? ''));

        if ($absolutePath === null) {
            Response::json([
                'message' => 'PDF preview is not available for this score.',
            ], 404);
        }

        Response::previewFile($absolutePath, 'application/pdf', 'preview.pdf');
    }

    private function ensureComposer(Request $request): void
    {
        $auth = $request->attribute('auth', []);
        $userId = (int) ($auth['sub'] ?? 0);
        $role = (string) (($this->users->findById($userId)['role'] ?? ''));

        if ($role !== 'composer') {
            Response::json([
                'message' => 'Composer access is required for this action.',
            ], 403);
        }
    }

    private function transformScores(array $scores, Request $request): array
    {
        return array_map(fn (array $score): array => $this->transformScore($score, $request), $scores);
    }

    private function transformScore(array $score, Request $request): array
    {
        $score['is_arranged'] = (bool) ($score['is_arranged'] ?? false);
        $score['image'] = $this->publicUrl((string) ($score['image'] ?? Score::DEFAULT_IMAGE), $request);
        $score['has_pdf_preview'] = (string) ($score['pdf_path'] ?? '') !== '';
        $score['approval_status'] = (string) ($score['approval_status'] ?? 'approved');
        unset($score['pdf_path']);

        return $score;
    }

    private function isPreviewRequest(Request $request): bool
    {
        if ($request->header('x-preview-request') !== '1') {
            return false;
        }

        return $this->isAllowedFrontendRequest($request);
    }

    private function isAllowedFrontendRequest(Request $request): bool
    {
        $allowedOrigin = (string) Config::get('CORS_ALLOWED_ORIGIN', '*');

        if ($allowedOrigin === '*' || $allowedOrigin === '') {
            return true;
        }

        $allowedOrigins = array_values(array_filter(array_map('trim', explode(',', $allowedOrigin))));
        $origin = (string) $request->header('origin', '');
        $referer = (string) $request->header('referer', '');

        if ($origin === '' && $referer === '') {
            return true;
        }

        foreach ($allowedOrigins as $allowed) {
            if ($origin !== '' && $origin === $allowed) {
                return true;
            }

            if ($referer !== '' && str_starts_with($referer, $allowed)) {
                return true;
            }
        }

        return false;
    }

    private function resolvePdfAbsolutePath(string $path): ?string
    {
        if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return null;
        }

        $normalizedPath = str_replace('\\', '/', $path);

        if (!str_starts_with($normalizedPath, 'stored/pdf/')) {
            return null;
        }

        $absolutePath = BASE_PATH . '/' . ltrim($normalizedPath, '/');

        return is_file($absolutePath) ? $absolutePath : null;
    }

    private function storeUploadedFile(array $file, string $directory, array $allowedExtensions): array
    {
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('The uploaded file could not be processed.');
        }

        $originalName = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new RuntimeException(sprintf(
                'Invalid file type. Allowed types: %s.',
                implode(', ', $allowedExtensions),
            ));
        }

        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to prepare the storage directory.');
        }

        $safeName = preg_replace('/[^A-Za-z0-9_-]/', '-', pathinfo($originalName, PATHINFO_FILENAME)) ?: 'file';
        $filename = sprintf('%s-%s.%s', $safeName, bin2hex(random_bytes(6)), $extension);
        $destination = $directory . '/' . $filename;

        if (!move_uploaded_file((string) $file['tmp_name'], $destination)) {
            throw new RuntimeException('Unable to save the uploaded file.');
        }

        $normalizedBasePath = str_replace('\\', '/', BASE_PATH);
        $normalizedDestination = str_replace('\\', '/', $destination);

        return [
            'absolute_path' => $destination,
            'relative_path' => ltrim(str_replace($normalizedBasePath, '', $normalizedDestination), '/'),
        ];
    }

    private function countPdfPages(string $path): int
    {
        $content = @file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException('Unable to read the uploaded PDF file.');
        }

        preg_match_all('/\/Type\s*\/Page\b/', $content, $matches);
        $pageCount = count($matches[0]);

        if ($pageCount > 0) {
            return $pageCount;
        }

        preg_match_all('/\/Count\s+(\d+)/', $content, $countMatches);
        $counts = array_map('intval', $countMatches[1] ?? []);

        if ($counts !== []) {
            return max($counts);
        }

        throw new RuntimeException('Unable to calculate the PDF page count.');
    }

    private function publicUrl(string $path, Request $request): string
    {
        if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if ($path === Score::DEFAULT_IMAGE) {
            return $path;
        }

        $normalizedPath = str_replace('\\', '/', $path);

        if (str_starts_with($normalizedPath, 'stored/images/')) {
            return $this->baseUrl($request) . '/api/score-image?path=' . rawurlencode($normalizedPath);
        }

        if (str_starts_with($normalizedPath, 'public/stored/images/')) {
            $normalizedPath = 'stored/images/' . basename($normalizedPath);
            return $this->baseUrl($request) . '/api/score-image?path=' . rawurlencode($normalizedPath);
        }

        if (str_starts_with($normalizedPath, 'public/')) {
            return $this->baseUrl($request) . '/' . ltrim(substr($normalizedPath, 7), '/');
        }

        $publicRoot = str_replace('\\', '/', BASE_PATH . '/public');

        if (str_starts_with($normalizedPath, $publicRoot)) {
            $relativePath = substr($normalizedPath, strlen($publicRoot));
            return $this->baseUrl($request) . $relativePath;
        }

        return $path;
    }

    private function baseUrl(Request $request): string
    {
        $scheme = $request->header('x-forwarded-proto')
            ?? (((($_SERVER['HTTPS'] ?? 'off') !== 'off')) ? 'https' : 'http');
        $host = $request->header('host', $_SERVER['HTTP_HOST'] ?? 'localhost');

        return sprintf('%s://%s', $scheme, $host);
    }
}
