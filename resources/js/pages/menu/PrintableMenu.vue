<template>
  <div class="printable-menu">
    <!-- Screen-only toolbar -->
    <div v-if="menu" class="print-toolbar no-print">
      <router-link to="/menus" class="back-link">
        <span class="icon"><i class="mdi mdi-arrow-left"></i></span>
        <span>All menus</span>
      </router-link>
      <button type="button" class="print-button" @click="printMenu">
        <span class="icon"><i class="mdi mdi-printer"></i></span>
        <span>Print menu</span>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="doc-state" role="status">
      <div class="spinner"></div>
      <p>Preparing your menu…</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="doc-state doc-state-error">
      <span class="state-icon"><i class="mdi mdi-alert-circle-outline"></i></span>
      <p>{{ error }}</p>
      <button type="button" class="print-button" @click="fetchPrintableMenu">Try again</button>
    </div>

    <!-- Menu Document -->
    <article v-else-if="menu" class="menu-document">
      <div class="brand-band">
        <img
          :src="logoUrl"
          alt="Hill Road Catering"
          class="brand-logo"
          width="2538"
          height="965"
        >
        <span class="brand-rule" aria-hidden="true"></span>
        <h1 class="menu-title">{{ menu.menu_name }}</h1>
        <p v-if="menu.description" class="menu-description">{{ menu.description }}</p>

        <div class="event-meta">
          <div class="meta-item">
            <span class="meta-label">Event size</span>
            <span class="meta-value">{{ menu.target_head_count }} <small>guests</small></span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Per guest</span>
            <span class="meta-value">{{ menu.pricing.formatted_price_per_person }}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Event total</span>
            <span class="meta-value">{{ menu.pricing.formatted_total_price }}</span>
          </div>
        </div>
      </div>

      <div class="doc-body">
        <section
          v-for="(segment, segmentIndex) in menu.segments"
          :key="segment.id"
          class="segment-section"
        >
          <div class="segment-heading">
            <span class="segment-index">{{ String(segmentIndex + 1).padStart(2, '0') }}</span>
            <h2 class="segment-name">{{ segment.name }}</h2>
            <span class="segment-rule" aria-hidden="true"></span>
          </div>

          <div class="table-wrap">
            <table class="items-table">
              <thead>
                <tr>
                  <th scope="col" class="item-name-header">Dish</th>
                  <th scope="col" class="quantity-header">Qty</th>
                  <th scope="col" class="price-header">Per guest</th>
                  <th scope="col" class="total-header">Total</th>
                  <th scope="col" class="notes-header">Notes</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in segment.items" :key="item.id">
                  <td class="item-name-cell">
                    <span class="item-name">{{ item.recipe_name }}</span>
                  </td>
                  <td class="quantity-cell">
                    <span class="quantity-badge">{{ item.formatted_quantity }}</span>
                  </td>
                  <td class="price-cell">{{ item.formatted_price_per_person }}</td>
                  <td class="total-cell"><strong>{{ item.formatted_total_price }}</strong></td>
                  <td class="notes-cell">
                    <template v-if="item.notes">{{ item.notes }}</template>
                    <span v-else class="empty-note">—</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- Grand Total -->
        <div class="grand-total">
          <div class="gt-copy">
            <p class="gt-eyebrow">Complete event package</p>
            <h3 class="gt-title">Served for {{ menu.target_head_count }} guests</h3>
            <p class="gt-sub">Premium catering prepared in-house, from first canapé to final course.</p>
          </div>
          <div class="gt-pricing">
            <div class="gt-line">
              <span>Per guest</span>
              <span>{{ menu.pricing.formatted_price_per_person }}</span>
            </div>
            <div v-if="menu.pricing.gst?.registered" class="gt-line">
              <span>Includes GST ({{ menu.pricing.gst.rate }}%)</span>
              <span>{{ menu.pricing.gst.formatted_amount_total }}</span>
            </div>
            <div class="gt-line gt-line-total">
              <span>Event total</span>
              <span>{{ menu.pricing.formatted_total_price }}</span>
            </div>
          </div>
        </div>

        <p class="doc-note">
          <template v-if="menu.pricing.gst?.note">
            {{ menu.pricing.gst.note }}
            <span class="note-sep" aria-hidden="true">·</span>
          </template>
          Prices in NZD.
        </p>

        <!-- Footer -->
        <div class="doc-footer">
          <p class="footer-tagline">Crafted with premium ingredients and professional service</p>
          <p class="footer-meta">
            Hill Road Catering
            <span aria-hidden="true">·</span>
            Menu prepared {{ generatedDate }}
          </p>
        </div>
      </div>
    </article>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

// Served from public/ so the logo keeps a stable URL in dev and in builds.
const logoUrl = '/images/hill-road-catering-logo-white.webp'

const route = useRoute()

// Data
const menu = ref(null)
const isLoading = ref(false)
const error = ref(null)

const generatedDate = computed(() =>
  new Date().toLocaleDateString('en-NZ', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
)

// Methods
const fetchPrintableMenu = async () => {
  isLoading.value = true
  error.value = null

  try {
    const menuId = route.params.id
    const response = await axios.get(`/api/menus/${menuId}/printable`)
    menu.value = response.data.menu
  } catch (fetchError) {
    console.error('Error fetching printable menu:', fetchError)
    error.value = fetchError.response?.data?.message || 'Failed to load printable menu'
  } finally {
    isLoading.value = false
  }
}

const printMenu = () => {
  window.print()
}

// Component lifecycle
onMounted(async () => {
  await fetchPrintableMenu()
})
</script>

<style scoped>
.printable-menu {
  /* Brand palette */
  --hr-green: #3a4633;
  --hr-green-deep: #2e392a;
  --hr-cream: #f4efe4;
  --hr-cream-deep: #e5ddcc;
  --hr-line: #e6e0d3;
  --hr-ink: #2b2f27;
  --hr-muted: #6c7264;

  --doc-serif: Georgia, 'Times New Roman', 'Iowan Old Style', serif;
  --doc-sans: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto,
    'Helvetica Neue', Arial, sans-serif;

  max-width: 1000px;
  margin: 0 auto 2.5rem;
  color: var(--hr-ink);
  font-family: var(--doc-sans);
}

/* ---------- Screen toolbar ---------- */
.print-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  color: #5f6657;
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  text-decoration: none;
}

.back-link .icon {
  width: auto;
  height: auto;
  font-size: 1rem;
}

.back-link:hover {
  color: var(--hr-green);
}

.print-button {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.55rem 1.1rem;
  border: 1px solid var(--hr-green);
  border-radius: 3px;
  background: var(--hr-green);
  color: #fff;
  font-family: var(--doc-sans);
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  cursor: pointer;
  transition: background-color 0.2s ease, border-color 0.2s ease;
}

.print-button .icon {
  width: auto;
  height: auto;
  font-size: 1rem;
}

.print-button:hover {
  background: var(--hr-green-deep);
  border-color: var(--hr-green-deep);
}

/* ---------- Document shell ---------- */
.menu-document {
  overflow: hidden;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 24px 60px -36px rgba(29, 37, 25, 0.55);
}

/* ---------- Brand band ---------- */
.brand-band {
  padding: 2.4rem 3rem 2rem;
  background:
    radial-gradient(130% 130% at 82% -20%, rgba(255, 255, 255, 0.13), rgba(255, 255, 255, 0) 55%),
    linear-gradient(140deg, #3a4633 0%, #33402d 55%, #2c3727 100%);
  color: #fff;
  text-align: center;
}

.brand-logo {
  display: block;
  width: 210px;
  max-width: 60%;
  height: auto;
  margin: 0 auto;
}

.brand-rule {
  display: block;
  width: 48px;
  height: 1px;
  margin: 1.35rem auto 1.15rem;
  background: rgba(245, 240, 230, 0.45);
}

.menu-title {
  margin: 0;
  color: #fff;
  font-family: var(--doc-serif);
  font-size: 2.35rem;
  font-weight: 400;
  line-height: 1.15;
  letter-spacing: 0.005em;
}

.menu-description {
  max-width: 570px;
  margin: 0.7rem auto 0;
  color: rgba(245, 240, 230, 0.78);
  font-size: 0.92rem;
  line-height: 1.65;
}

.event-meta {
  display: flex;
  justify-content: center;
  max-width: 640px;
  margin: 1.7rem auto 0;
  padding-top: 1.25rem;
  border-top: 1px solid rgba(245, 240, 230, 0.18);
}

.meta-item {
  flex: 1;
  padding: 0 1.25rem;
  border-right: 1px solid rgba(245, 240, 230, 0.18);
}

.meta-item:last-child {
  border-right: 0;
}

.meta-label {
  display: block;
  margin-bottom: 0.4rem;
  color: rgba(245, 240, 230, 0.7);
  font-size: 0.6rem;
  letter-spacing: 0.18em;
  text-transform: uppercase;
}

.meta-value {
  display: block;
  color: #fff;
  font-family: var(--doc-serif);
  font-size: 1.28rem;
  line-height: 1.2;
  white-space: nowrap;
}

.meta-value small {
  color: rgba(245, 240, 230, 0.65);
  font-family: var(--doc-sans);
  font-size: 0.7rem;
}

/* ---------- Body ---------- */
.doc-body {
  padding: 2.4rem 3rem 2.2rem;
}

/* ---------- Segments ---------- */
.segment-section {
  margin: 0 0 1.9rem;
}

.segment-section:last-of-type {
  margin-bottom: 1.4rem;
}

.segment-heading {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  margin-bottom: 0.85rem;
}

.segment-index {
  flex: none;
  padding: 0.24rem 0.5rem;
  border: 1px solid var(--hr-cream-deep);
  border-radius: 999px;
  background: var(--hr-cream);
  color: var(--hr-green);
  font-size: 0.62rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  line-height: 1;
}

.segment-name {
  flex: none;
  margin: 0;
  color: var(--hr-green);
  font-family: var(--doc-serif);
  font-size: 1.4rem;
  font-weight: 400;
  line-height: 1.2;
}

.segment-rule {
  flex: 1;
  height: 1px;
  background: linear-gradient(to right, var(--hr-cream-deep), rgba(229, 221, 204, 0));
}

/* ---------- Items table ---------- */
.table-wrap {
  overflow-x: auto;
}

.items-table {
  display: table;
  width: 100%;
  min-width: 600px;
  border-collapse: collapse;
}

.items-table thead {
  display: table-header-group;
}

.items-table tbody {
  display: table-row-group;
}

.items-table tr {
  display: table-row;
  border: 0;
}

.items-table th,
.items-table td {
  display: table-cell;
}

.items-table th {
  padding: 0.62rem 0.7rem;
  border-top: 1px solid var(--hr-cream-deep);
  border-bottom: 1px solid var(--hr-cream-deep);
  background: var(--hr-cream);
  color: #59634f;
  font-size: 0.6rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-align: left;
  text-transform: uppercase;
}

.items-table td {
  padding: 0.68rem 0.7rem;
  border-bottom: 1px solid #eee9dd;
  color: var(--hr-ink);
  font-size: 0.84rem;
  text-align: left;
  vertical-align: middle;
}

.items-table tbody tr {
  background: #fff;
}

.items-table tbody tr:nth-child(even) {
  background: #fcfaf5;
}

.items-table tbody tr:hover td {
  background-color: transparent;
}

.items-table tbody tr td:before {
  content: none;
}

.item-name-header,
.item-name-cell {
  width: 35%;
}

.item-name-cell {
  text-align: left;
}

.item-name {
  color: var(--hr-ink);
  font-family: var(--doc-serif);
  font-size: 0.98rem;
}

.quantity-header,
.quantity-cell {
  width: 11%;
}

.quantity-header,
.quantity-cell {
  text-align: center;
}

.quantity-badge {
  display: inline-block;
  padding: 0.14rem 0.48rem;
  white-space: nowrap;
  border-radius: 3px;
  background: var(--hr-cream);
  color: #55604b;
  font-size: 0.7rem;
  font-weight: 600;
  letter-spacing: 0.02em;
}

.price-header,
.price-cell {
  width: 17%;
  text-align: right;
}

.price-cell {
  color: #5d6556;
  font-size: 0.82rem;
}

.total-header,
.total-cell {
  width: 17%;
  text-align: right;
}

.total-cell strong {
  color: var(--hr-green);
  font-weight: 600;
}

.notes-header,
.notes-cell {
  width: 20%;
}

.notes-cell {
  color: var(--hr-muted);
  font-size: 0.76rem;
  font-style: italic;
  line-height: 1.45;
}

.empty-note {
  color: #b7b3a8;
  font-style: normal;
}

/* ---------- Grand total ---------- */
.grand-total {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 2rem;
  padding: 1.8rem 2rem;
  border-radius: 4px;
  background: linear-gradient(120deg, var(--hr-green) 0%, #303b2b 100%);
  color: #fff;
}

.gt-copy {
  max-width: 50%;
}

.gt-eyebrow {
  margin: 0 0 0.45rem;
  color: rgba(245, 240, 230, 0.62);
  font-size: 0.6rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
}

.gt-title {
  margin: 0;
  color: #fff;
  font-family: var(--doc-serif);
  font-size: 1.45rem;
  font-weight: 400;
  line-height: 1.25;
}

.gt-sub {
  margin: 0.5rem 0 0;
  color: rgba(245, 240, 230, 0.7);
  font-size: 0.78rem;
  line-height: 1.5;
}

.gt-pricing {
  flex: none;
  width: 260px;
  font-variant-numeric: tabular-nums;
}

.gt-line {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.28rem 0;
  color: rgba(255, 255, 255, 0.82);
  font-size: 0.8rem;
}

.gt-line-total {
  margin-top: 0.45rem;
  padding-top: 0.55rem;
  border-top: 1px solid rgba(245, 240, 230, 0.25);
  color: #fff;
  font-size: 0.72rem;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.gt-line-total span:last-child {
  font-family: var(--doc-serif);
  font-size: 1.65rem;
  letter-spacing: 0;
  line-height: 1;
  text-transform: none;
}

/* ---------- Notes & footer ---------- */
.doc-note {
  margin: 1rem 0 0;
  color: var(--hr-muted);
  font-size: 0.7rem;
  line-height: 1.6;
}

.note-sep {
  padding: 0 0.35rem;
}

.doc-footer {
  margin-top: 1.8rem;
  padding-top: 1.2rem;
  border-top: 1px solid var(--hr-line);
  text-align: center;
}

.footer-tagline {
  margin: 0;
  color: var(--hr-green);
  font-family: var(--doc-serif);
  font-size: 0.92rem;
  font-style: italic;
}

.footer-meta {
  margin: 0.5rem 0 0;
  color: var(--hr-muted);
  font-size: 0.62rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.footer-meta span {
  padding: 0 0.4rem;
}

/* ---------- Loading & error ---------- */
.doc-state {
  padding: 3rem;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 24px 60px -36px rgba(29, 37, 25, 0.55);
  color: var(--hr-muted);
  text-align: center;
}

.doc-state p {
  margin-top: 0.75rem;
}

.doc-state-error {
  color: #8a3a33;
}

.state-icon {
  color: #b4453d;
  font-size: 2rem;
}

.spinner {
  width: 34px;
  height: 34px;
  margin: 0 auto;
  border: 3px solid var(--hr-cream);
  border-top-color: var(--hr-green);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* ---------- Responsive ---------- */
@media (max-width: 860px) {
  .brand-band {
    padding: 1.9rem 1.5rem 1.6rem;
  }

  .menu-title {
    font-size: 1.9rem;
  }

  .event-meta {
    flex-wrap: wrap;
    gap: 0.9rem 0;
  }

  .meta-item {
    flex: 1 1 33.333%;
    padding: 0 0.6rem;
  }

  .doc-body {
    padding: 1.8rem 1.5rem 1.6rem;
  }

  .grand-total {
    flex-direction: column;
    align-items: flex-start;
    gap: 1.2rem;
  }

  .gt-copy {
    max-width: none;
  }

  .gt-pricing {
    width: 100%;
  }
}

@media (max-width: 560px) {
  .brand-logo {
    width: 170px;
  }

  .menu-title {
    font-size: 1.6rem;
  }

  .menu-description {
    font-size: 0.85rem;
  }

  .event-meta {
    gap: 0;
  }

  .meta-item {
    flex: 1 1 100%;
    padding: 0.55rem 0;
    border-right: 0;
    border-bottom: 1px solid rgba(245, 240, 230, 0.15);
  }

  .meta-item:last-child {
    border-bottom: 0;
  }

  .segment-name {
    font-size: 1.15rem;
  }

  .doc-body {
    padding: 1.4rem 1rem 1.2rem;
  }

  .print-toolbar {
    gap: 0.5rem;
  }

  .print-button {
    padding: 0.5rem 0.8rem;
  }
}

/* ---------- Narrow screens: turn the price table into stacked cards ---------- */
@media (max-width: 620px) {
  .table-wrap {
    overflow: visible;
  }

  .items-table {
    display: block;
    min-width: 0;
  }

  .items-table thead {
    display: none;
  }

  .items-table tbody {
    display: block;
  }

  .items-table tr {
    display: grid;
    grid-template-areas:
      'name name name'
      'qty price total'
      'notes notes notes';
    grid-template-columns: repeat(3, 1fr);
    row-gap: 0.45rem;
    margin-bottom: 0.5rem;
    padding: 0.85rem 0.9rem;
    border: 1px solid #eee9dd;
    border-radius: 4px;
    background: #fff;
  }

  .items-table tbody tr:nth-child(even) {
    background: #fcfaf5;
  }

  .items-table tbody tr:last-child {
    margin-bottom: 0;
  }

  .items-table td {
    display: block;
    width: auto;
    padding: 0;
    border: 0;
  }

  .items-table tbody tr td:before {
    padding-right: 0;
  }

  .item-name-cell {
    grid-area: name;
    margin-bottom: 0.15rem;
  }

  .item-name {
    font-size: 1.02rem;
  }

  .quantity-cell {
    grid-area: qty;
    text-align: left;
  }

  .price-cell {
    grid-area: price;
    text-align: left;
  }

  .total-cell {
    grid-area: total;
    text-align: right;
  }

  .notes-cell {
    grid-area: notes;
    margin-top: 0.1rem;
    font-size: 0.8rem;
  }

  .items-table tbody tr td.quantity-cell::before,
  .items-table tbody tr td.price-cell::before,
  .items-table tbody tr td.total-cell::before {
    display: block;
    margin-bottom: 0.2rem;
    padding: 0;
    color: var(--hr-muted);
    font-family: var(--doc-sans);
    font-size: 0.55rem;
    font-style: normal;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-align: inherit;
    text-transform: uppercase;
  }

  .items-table tbody tr td.quantity-cell::before {
    content: 'Qty';
  }

  .items-table tbody tr td.price-cell::before {
    content: 'Per guest';
  }

  .items-table tbody tr td.total-cell::before {
    content: 'Total';
  }
}

/* ---------- Print ---------- */
@media print {
  .printable-menu {
    max-width: none;
    margin: 0;
  }

  .print-toolbar {
    display: none !important;
  }

  .menu-document {
    overflow: visible;
    border-radius: 0;
    background: #fff;
    box-shadow: none;
  }

  .brand-band {
    break-after: avoid;
    padding: 16px 22px 14px;
    background: #3a4633;
  }

  .brand-logo {
    width: 150px;
    max-width: 50%;
  }

  .brand-rule {
    margin: 10px auto 8px;
    background: rgba(245, 240, 230, 0.4);
  }

  .menu-title {
    font-size: 24px;
  }

  .menu-description {
    max-width: 480px;
    margin-top: 5px;
    color: rgba(255, 255, 255, 0.82);
    font-size: 10px;
    line-height: 1.5;
  }

  .event-meta {
    max-width: none;
    margin-top: 10px;
    padding-top: 9px;
  }

  .meta-item {
    padding: 0 14px;
  }

  .meta-label {
    margin-bottom: 3px;
    font-size: 8px;
  }

  .meta-value {
    font-size: 14px;
  }

  .meta-value small {
    font-size: 9px;
  }

  .doc-body {
    padding: 16px 22px 10px;
  }

  .segment-section {
    margin-bottom: 12px;
  }

  .segment-heading {
    gap: 7px;
    margin-bottom: 6px;
    break-after: avoid;
  }

  .segment-index {
    padding: 2px 5px;
    font-size: 8px;
  }

  .segment-name {
    font-size: 15px;
  }

  .table-wrap {
    overflow: visible;
  }

  .items-table {
    min-width: 0;
  }

  .items-table th {
    padding: 5px 6px;
    font-size: 8.5px;
  }

  .items-table td {
    padding: 5px 6px;
    font-size: 10.5px;
    line-height: 1.35;
  }

  .items-table tr {
    break-inside: avoid;
  }

  .item-name {
    font-size: 11.5px;
  }

  .quantity-badge {
    padding: 1px 4px;
    font-size: 9.5px;
  }

  .price-cell {
    font-size: 10.5px;
  }

  .notes-cell {
    font-size: 9.5px;
  }

  .grand-total {
    flex-direction: row;
    align-items: center;
    gap: 16px;
    break-inside: avoid;
    padding: 12px 16px;
    border-radius: 0;
    background: #3a4633;
  }

  .gt-copy {
    max-width: 50%;
  }

  .gt-eyebrow {
    margin-bottom: 3px;
    font-size: 7px;
  }

  .gt-title {
    font-size: 14px;
  }

  .gt-sub {
    display: none;
  }

  .gt-pricing {
    width: 220px;
  }

  .gt-line {
    padding: 2px 0;
    font-size: 9px;
  }

  .gt-line-total {
    margin-top: 3px;
    padding-top: 4px;
  }

  .gt-line-total span:last-child {
    font-size: 16px;
  }

  .doc-note {
    margin-top: 7px;
    font-size: 9px;
  }

  .doc-footer {
    margin-top: 10px;
    padding-top: 8px;
    break-inside: avoid;
  }

  .footer-tagline {
    font-size: 10px;
  }

  .footer-meta {
    margin-top: 3px;
    font-size: 7px;
  }
}
</style>

<!-- Unscoped: strip the admin shell and set the paper size when printing. -->
<style>
@media print {
  @page {
    size: A4;
    margin: 10mm;
  }

  html,
  body {
    margin: 0 !important;
    padding: 0 !important;
    overflow: visible !important;
    background: #fff !important;
  }

  #app,
  #app > *,
  .main-section,
  .section {
    width: auto !important;
    max-width: none !important;
    height: auto !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  nav,
  aside,
  footer,
  .navbar,
  .no-print {
    display: none !important;
  }

  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>
