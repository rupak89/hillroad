<template>
  <div class="printable-menu">
    <!-- Print Header -->
    <div v-if="menu" class="print-header no-print card">
      <div class="level">
        <div class="level-left">
          <header class="card-header">
            <p class="card-header-title">
            <span class="icon"><i class="mdi mdi-food"></i></span>
            {{ menu.menu_name }}
            </p>
            <div class="level-right">
            <button @click="printMenu" class="button is-primary">
                <span class="icon">
                <i class="mdi mdi-printer"></i>
                </span>
                <span>Print Menu</span>
            </button>
            </div>
          </header>
        </div>

      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="box has-text-centered">
      <div class="spinner"></div>
      <p>Loading menu...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="box has-background-danger-light">
      <p class="has-text-danger">{{ error }}</p>
    </div>

    <!-- Menu Content -->
    <div v-else-if="menu" class="menu-content">
      <!-- Menu Header -->
      <div class="menu-header has-text-centered mb-1">
        <h1 class="title is-1 has-text-primary mb-3">{{ menu.menu_name }}</h1>
        <p v-if="menu.description" class="subtitle is-4 has-text-grey-dark mb-4">{{ menu.description }}</p>

        <div class="event-info">
          <div class="info-cards-row">
            <div class="info-card">
              <span class="icon is-large has-text-primary">
                <i class="mdi mdi-account-group mdi-36px"></i>
              </span>
              <div class="info-content">
                <p class="heading">Event Size</p>
                <p class="title is-4 has-text-primary">{{ menu.target_head_count }} Guests</p>
              </div>
            </div>
            <div class="info-card">
              <span class="icon is-large has-text-success">
                <i class="mdi mdi-currency-usd mdi-36px"></i>
              </span>
              <div class="info-content">
                <p class="heading">Per Person</p>
                <p class="title is-4 has-text-success">{{ menu.pricing.formatted_price_per_person }}</p>
              </div>
            </div>
            <div class="info-card">
              <span class="icon is-large has-text-info">
                <i class="mdi mdi-calculator mdi-36px"></i>
              </span>
              <div class="info-content">
                <p class="heading">Total Event</p>
                <p class="title is-4 has-text-info">{{ menu.pricing.formatted_total_price }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Menu Segments -->
      <div class="menu-segments">
        <div
          v-for="(segment, segmentIndex) in menu.segments"
          :key="segment.id"
          class="segment-section mb-6"
        >
          <div class="segment-header">
            <h2 class="title is-3 has-text-primary has-text-centered segment-title">
              <span class="segment-number">{{ segmentIndex + 1 }}.</span>
              {{ segment.name }}
            </h2>
            <div class="segment-divider"></div>
          </div>

          <div class="segment-items">
            <table class="table is-fullwidth is-hoverable items-table">
              <thead>
                <tr>
                  <th class="item-name-header">Item</th>
                  <th class="quantity-header">Quantity</th>
                  <th class="price-header">Price per Person</th>
                  <th class="total-header">Total ({{ menu.target_head_count }} guests)</th>
                  <th class="notes-header">Notes</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="item in segment.items"
                  :key="item.id"
                  class="item-row"
                >
                  <td class="item-name-cell">
                    <div class="item-name-content">
                      <span class="icon is-small has-text-primary">
                        <i class="mdi mdi-chef-hat"></i>
                      </span>
                      <strong>{{ item.recipe_name }}</strong>
                    </div>
                  </td>
                  <td class="quantity-cell">
                    <span class="quantity-badge">{{ item.formatted_quantity }}</span>
                  </td>
                  <td class="price-cell">
                    <span class="price-amount">{{ item.formatted_price_per_person }}</span>
                  </td>
                  <td class="total-cell">
                    <span class="total-amount">{{ item.formatted_total_price }}</span>
                  </td>
                  <td class="notes-cell">
                    <div v-if="item.notes" class="notes-content">
                      <span class="icon is-small has-text-info">
                        <i class="mdi mdi-note-text"></i>
                      </span>
                      <span class="notes-text">{{ item.notes }}</span>
                    </div>
                    <span v-else class="has-text-grey-light">—</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Grand Total Section -->
      <div class="grand-total-section mt-6">
        <div class="grand-total-card">
          <div class="columns is-vcentered">
            <div class="column">
              <h3 class="title is-3 has-text-white mb-2">
                <span class="icon">
                  <i class="mdi mdi-star"></i>
                </span>
                Complete Event Package
              </h3>
              <p class="subtitle is-5 has-text-white-bis">
                Premium catering service for {{ menu.target_head_count }} guests
              </p>
            </div>
            <div class="column is-narrow">
              <div class="price-breakdown">
                <div class="price-line">
                  <span class="price-label">Per Person:</span>
                  <span class="price-value">{{ menu.pricing.formatted_price_per_person }}</span>
                </div>
                <div class="price-line main-price">
                  <span class="price-label">Grand Total:</span>
                  <span class="price-value">{{ menu.pricing.formatted_total_price }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="menu-footer has-text-centered mt-6">
        <div class="footer-content">
          <p class="subtitle is-6 has-text-grey">
            <span class="icon">
              <i class="mdi mdi-chef-hat"></i>
            </span>
            <em>Crafted with premium ingredients and professional service</em>
          </p>
          <p class="has-text-grey-light mt-2">
            <span class="icon is-small">
              <i class="mdi mdi-calendar"></i>
            </span>
            Generated on {{ new Date().toLocaleDateString() }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'PrintableMenu',

  data() {
    return {
      menu: null,
      isLoading: false,
      error: null,
    }
  },

  async mounted() {
    await this.fetchPrintableMenu()
  },

  methods: {
    async fetchPrintableMenu() {
      this.isLoading = true
      this.error = null

      try {
        const menuId = this.$route.params.id
        const response = await axios.get(`/api/menus/${menuId}/printable`)
        this.menu = response.data.menu
      } catch (error) {
        console.error('Error fetching printable menu:', error)
        this.error = error.response?.data?.message || 'Failed to load printable menu'
      } finally {
        this.isLoading = false
      }
    },

    printMenu() {
      window.print()
    }
  }
}
</script>

<style scoped>
.printable-menu {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Georgia', serif;
}

.menu-header {
  border-bottom: 4px solid var(--bulma-primary);
  padding-bottom: 0.4rem;
  margin-bottom: 0.2rem;
}

.event-info {
  margin-bottom: 0.5rem;
}

.info-cards-row {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: nowrap;
}

.info-card {
  display: flex;
  align-items: center;
  background: white;
  padding: 1.25rem;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  min-width: 220px;
  flex: 1;
  max-width: 280px;
  transition: all 0.3s ease;
}

.info-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.15);
}

.info-content {
  margin-left: 1rem;
  text-align: left;
  flex: 1;
}

.info-content .heading {
  margin-bottom: 0.25rem;
  font-size: 0.875rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--bulma-grey);
}

.info-content .title {
  margin: 0;
  line-height: 1.2;
}

.segment-header {
  margin-bottom: 0.5rem;
}

.segment-header {
  margin-bottom: 0.5rem;
}

.segment-title {
  position: relative;
  padding-bottom: 1rem;
}

.segment-number {
  color: var(--bulma-primary);
  font-weight: bold;
  margin-right: 0.5rem;
}

.segment-divider {
  height: 2px;
  background: linear-gradient(to right, var(--bulma-primary), var(--bulma-primary-light), transparent);
  margin: 0 auto;
  width: 60%;
}

.segment-items {
  margin-bottom: 0.5rem;
}

/* Table Styles */
.items-table {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  border: none !important;
}

.items-table thead {
  background: linear-gradient(135deg, var(--bulma-primary), var(--bulma-primary-light));
}

  .items-table thead th {
    color: #333 !important;
    font-weight: 600 !important;
    padding: 5px 4px !important;
    border: 1px solid #ddd !important;
    font-size: 11px !important;
    text-align: center !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    display: table-cell !important;
    background: #f5f5f5 !important;
  }.item-name-header {
  text-align: left !important;
  width: 30%;
}

.quantity-header {
  width: 15%;
}

.price-header {
  width: 20%;
}

.total-header {
  width: 20%;
}

.notes-header {
  width: 15%;
}

.items-table tbody tr {
  border-bottom: 1px solid #f5f5f5;
  transition: all 0.3s ease;
}

.items-table tbody tr:hover {
  background-color: #f8f9fa;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.items-table tbody tr:last-child {
  border-bottom: none;
}

.items-table tbody td {
  padding: 1.25rem 1rem;
  border: none !important;
  vertical-align: middle;
}

.item-name-cell {
  text-align: left;
}

.item-name-content {
  display: flex;
  align-items: center;
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--bulma-dark);
}

.item-name-content .icon {
  margin-right: 0.75rem;
}

.quantity-cell {
  text-align: center;
}

.quantity-badge {
  background: var(--bulma-primary-light);
  color: var(--bulma-primary-dark);
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  font-weight: 500;
  font-size: 0.9rem;
}

.price-cell {
  text-align: center;
}

.price-amount {
  font-size: 1.25rem;
  font-weight: bold;
  color: var(--bulma-success);
}

.total-cell {
  text-align: center;
}

.total-amount {
  font-size: 1.25rem;
  font-weight: bold;
  color: var(--bulma-info);
}

.notes-cell {
  text-align: center;
}

.notes-content {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
}

.notes-content .icon {
  margin-right: 0.5rem;
}

.notes-text {
  font-style: italic;
  color: var(--bulma-grey-darker);
  max-width: 150px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.grand-total-section {
  margin-top: 3rem;
}

.grand-total-card {
  background: linear-gradient(135deg, var(--bulma-primary), var(--bulma-info));
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}

.price-breakdown {
  background: rgba(255,255,255,0.1);
  padding: 1.5rem;
  border-radius: 12px;
  backdrop-filter: blur(10px);
}

.price-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
  color: white;
}

.price-line:last-child {
  margin-bottom: 0;
}

.price-line.main-price {
  border-top: 2px solid rgba(255,255,255,0.3);
  padding-top: 0.75rem;
  font-size: 1.25rem;
  font-weight: bold;
}

.price-line .price-label {
  font-size: 1rem;
  opacity: 0.9;
}

.price-line .price-value {
  font-size: 1.5rem;
  font-weight: bold;
}

.menu-footer {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 2px solid var(--bulma-grey-lighter);
}

.footer-content {
  opacity: 0.8;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid var(--bulma-primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Print Styles */
@media print {
  /* Hide all navigation and UI elements */
  .no-print,
  nav,
  .navbar,
  .navbar-brand,
  .navbar-menu,
  .navbar-burger,
  .breadcrumb,
  .pagination,
  .tabs,
  .button,
  .dropdown,
  .modal,
  .notification,
  .level,
  .hero,
  header,
  footer,
  aside,
  .sidebar,
  .menu,
  .panel {
    display: none !important;
  }

  /* Hide any elements with common navigation classes */
  .nav,
  .navigation,
  .header,
  .top-bar,
  .menu-bar,
  .hamburger,
  .burger,
  .toggle,
  .mobile-menu {
    display: none !important;
  }

  /* Ensure table content is visible */
  .segment-items table,
  .items-table,
  .table {
    display: table !important;
    visibility: visible !important;
  }

  .items-table thead,
  .table thead {
    display: table-header-group !important;
  }

  .items-table tbody,
  .table tbody {
    display: table-row-group !important;
  }

  .items-table tr,
  .table tr {
    display: table-row !important;
  }

  .items-table th,
  .items-table td,
  .table th,
  .table td {
    display: table-cell !important;
  }

  /* Reset page layout for print */
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  html, body {
    margin: 0 !important;
    padding: 0 !important;
    background: white !important;
    font-size: 11px !important;
    line-height: 1.3 !important;
  }

  .printable-menu {
    max-width: none !important;
    margin: 0 !important;
    padding: 12px !important;
    background: white !important;
  }

  /* Header adjustments */
  .menu-header {
    margin-bottom: 1rem !important;
    padding-bottom: 0.75rem !important;
    border-bottom: 2px solid #333 !important;
  }

  .menu-header .title {
    font-size: 22px !important;
    margin-bottom: 6px !important;
  }

  .menu-header .subtitle {
    font-size: 14px !important;
    margin-bottom: 6px !important;
  }  /* Info cards in single row for print */
  .event-info {
    margin-bottom: 1rem !important;
  }

  .info-cards-row {
    display: flex !important;
    justify-content: center !important;
    gap: 12px !important;
    flex-wrap: nowrap !important;
  }

  .info-card {
    break-inside: avoid !important;
    box-shadow: none !important;
    border: 1px solid #ddd !important;
    margin: 0 !important;
    padding: 8px !important;
    min-width: 140px !important;
    max-width: 180px !important;
    flex: 1 !important;
    background: white !important;
  }

  .info-card .icon {
    color: #333 !important;
  }

  .info-content {
    margin-left: 8px !important;
  }

  .info-content .heading {
    font-size: 10px !important;
    color: #666 !important;
    margin-bottom: 3px !important;
  }

  .info-content .title {
    font-size: 14px !important;
    color: #333 !important;
    margin: 0 !important;
  }

  /* Items table for print */
  .items-table {
    background: white !important;
    border: 1px solid #ddd !important;
    margin-bottom: 12px !important;
    width: 100% !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    table-layout: fixed !important;
    border-collapse: collapse !important;
    display: table !important;
  }

  .items-table thead {
    background: #f5f5f5 !important;
    display: table-header-group !important;
  }

  .items-table tbody {
    display: table-row-group !important;
  }

  .items-table tr {
    display: table-row !important;
  }

  .items-table th,
  .items-table td {
    display: table-cell !important;
  }

  /* Ensure table content is visible */
  .segment-items table,
  .items-table,
  .items-table *,
  .table,
  .table * {
    display: initial !important;
    visibility: visible !important;
  }

  .item-name-header {
    width: 35% !important;
    text-align: left !important;
  }

  .quantity-header {
    width: 15% !important;
  }

  .price-header {
    width: 20% !important;
  }

  .total-header {
    width: 20% !important;
  }

  .notes-header {
    width: 10% !important;
  }

  .items-table tbody tr {
    break-inside: avoid !important;
    page-break-inside: avoid !important;
    height: 26px !important;
    max-height: 26px !important;
    display: table-row !important;
  }

  .items-table tbody td {
    padding: 4px 3px !important;
    border: 1px solid #ddd !important;
    font-size: 11px !important;
    color: #333 !important;
    text-align: center !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    height: 26px !important;
    max-height: 26px !important;
    line-height: 1.3 !important;
    display: table-cell !important;
  }

  .item-name-cell {
    text-align: left !important;
    padding-left: 6px !important;
  }

  .item-name-content {
    font-size: 11px !important;
    display: inline !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
  }

  .item-name-content .icon {
    display: none !important;
  }

  .quantity-badge {
    background: transparent !important;
    color: #333 !important;
    padding: 0 !important;
    font-size: 11px !important;
    border-radius: 0 !important;
    display: inline !important;
    border: none !important;
  }

  .price-amount {
    font-size: 11px !important;
    color: #333 !important;
    font-weight: bold !important;
  }

  .total-amount {
    font-size: 11px !important;
    color: #333 !important;
    font-weight: bold !important;
  }

  .notes-content {
    font-size: 10px !important;
    display: inline !important;
  }

  .notes-content .icon {
    display: none !important;
  }

  .notes-text {
    font-size: 10px !important;
    color: #666 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    max-width: none !important;
  }

  /* Segment headers */
  .segment-section {
    break-inside: avoid !important;
    margin-bottom: 1rem !important;
    page-break-inside: avoid !important;
  }

  .segment-items {
    display: block !important;
    margin-bottom: 0.5rem !important;
  }

  .segment-title {
    font-size: 16px !important;
    margin-bottom: 8px !important;
    padding-bottom: 4px !important;
    border-bottom: 2px solid #333 !important;
  }

  .segment-divider {
    display: none !important;
  }

  /* Grand total section */
  .grand-total-card {
    break-inside: avoid !important;
    background: #f5f5f5 !important;
    color: #333 !important;
    border: 1px solid #333 !important;
    padding: 8px !important;
    margin-top: 12px !important;
  }

  .grand-total-card .title,
  .grand-total-card .subtitle {
    color: #333 !important;
  }

  .grand-total-card .title {
    font-size: 16px !important;
    margin-bottom: 6px !important;
  }

  .grand-total-card .subtitle {
    font-size: 12px !important;
  }

  .price-breakdown {
    background: white !important;
    border: 1px solid #333 !important;
    padding: 8px !important;
  }

  .price-line {
    color: #333 !important;
    margin-bottom: 3px !important;
    font-size: 11px !important;
  }

  .price-line .price-value {
    font-size: 13px !important;
  }

  /* Footer */
  .menu-footer {
    margin-top: 15px !important;
    padding-top: 10px !important;
    border-top: 1px solid #ddd !important;
    font-size: 10px !important;
  }

  /* Text color overrides */
  .title, .subtitle {
    color: #333 !important;
  }

  .has-text-success {
    color: #2d5f3f !important;
  }

  .has-text-primary {
    color: #2d4a7a !important;
  }

  .has-text-info {
    color: #2d6a8a !important;
  }

  .has-text-grey,
  .has-text-grey-dark,
  .has-text-grey-darker {
    color: #666 !important;
  }

  /* Page breaks */
  .segment-section {
    page-break-inside: avoid !important;
  }

  .grand-total-section {
    page-break-inside: avoid !important;
  }

  /* Ensure no page breaks within item rows */
  .items-row {
    page-break-inside: avoid !important;
  }
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .info-card {
    min-width: auto;
    flex-direction: column;
    text-align: center;
  }

  .info-content {
    margin-left: 0;
    margin-top: 0.5rem;
  }

  .item-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .item-price {
    text-align: left;
    margin-top: 0.5rem;
  }

  .grand-total-card .columns {
    flex-direction: column;
  }
}
</style>
