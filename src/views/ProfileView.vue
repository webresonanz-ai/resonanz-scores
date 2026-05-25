<template>
  <div class="container py-5 mt-5">
    <div class="row">
      <div class="col-lg-4">
        <div
          class="profile-sidebar p-4"
          style="
            background: linear-gradient(135deg, var(--darker) 0%, var(--accent) 100%);
            border: 1px solid rgba(201, 168, 76, 0.3);
            border-radius: 15px;
          "
        >
          <div class="text-center mb-4">
            <img
              src="https://picsum.photos/150/150?random=100"
              class="rounded-circle mb-3"
              width="120"
              height="120"
              style="border: 3px solid var(--gold); object-fit: cover"
              alt="Profile"
            />
            <h4 class="text-gold">John Doe</h4>
            <p class="text-muted">Music Enthusiast</p>
          </div>
          <hr class="border-gold" />
          <ul class="list-unstyled">
            <li class="mb-3">
              <i class="bi bi-envelope text-gold me-2"></i>
              <span class="text-muted">john.doe@email.com</span>
            </li>
            <li class="mb-3">
              <i class="bi bi-geo-alt text-gold me-2"></i>
              <span class="text-muted">New York, USA</span>
            </li>
            <li class="mb-3">
              <i class="bi bi-calendar text-gold me-2"></i>
              <span class="text-muted">Member since 2024</span>
            </li>
          </ul>
          <button class="btn btn-outline-gold w-100">
            <i class="bi bi-pencil me-1"></i> Edit Profile
          </button>
        </div>
      </div>

      <div class="col-lg-8">
        <div
          class="p-4"
          style="
            background: linear-gradient(135deg, var(--darker) 0%, var(--accent) 100%);
            border: 1px solid rgba(201, 168, 76, 0.3);
            border-radius: 15px;
          "
        >
          <h4 class="text-gold mb-4"><i class="bi bi-clock-history me-2"></i>Purchase History</h4>

          <div
            class="purchase-item mb-3 p-3"
            style="background: rgba(201, 168, 76, 0.05); border-radius: 10px"
          >
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-gold mb-1">Moonlight Sonata</h6>
                <p class="text-muted mb-0 small">Purchased on Jan 15, 2024</p>
              </div>
              <span class="price-tag">$19.99</span>
            </div>
          </div>

          <div
            class="purchase-item mb-3 p-3"
            style="background: rgba(201, 168, 76, 0.05); border-radius: 10px"
          >
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-gold mb-1">Clair de Lune</h6>
                <p class="text-muted mb-0 small">Purchased on Dec 20, 2023</p>
              </div>
              <span class="price-tag">$14.99</span>
            </div>
          </div>

          <div
            class="purchase-item p-3"
            style="background: rgba(201, 168, 76, 0.05); border-radius: 10px"
          >
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-gold mb-1">Nocturne in E-flat Major</h6>
                <p class="text-muted mb-0 small">Purchased on Nov 5, 2023</p>
              </div>
              <span class="price-tag">$17.99</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup></script>
