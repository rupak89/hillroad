<template>
  <section class="section main-section">
    <router-link to="/menus" class="button is-small">
      <span class="icon"><i class="mdi mdi-arrow-left"></i></span>
      <span class="sm:hidden lg:inline">Back</span>
    </router-link>

    <div class="card mb-6">
      <header class="card-header">
        <p class="card-header-title">
          <span class="icon"><i class="mdi mdi-food"></i></span>
          {{ isEditMode ? 'Edit Menu' : 'Add Menu' }}
        </p>
      </header>
      <div class="card-content">
        <div v-if="isLoading" class="has-text-centered py-6">
          <div class="button is-loading is-static">Loading menu data...</div>
        </div>
        <form v-else @submit.prevent="submitForm">
          <!-- Menu Basic Info -->
          <div class="columns">
            <div class="column">
              <div class="field">
                <label class="label">Menu Name</label>
                <div class="control">
                  <input
                    class="input"
                    :class="{ 'is-danger': errors.name }"
                    type="text"
                    placeholder="e.g. Wedding Package A"
                    v-model="menu.name">
                </div>
                <p v-if="errors.name" class="help is-danger">{{ errors.name[0] }}</p>
              </div>
            </div>
            <div class="column">
              <div class="field">
                <label class="label">Target Head Count</label>
                <div class="control">
                  <input
                    class="input"
                    :class="{ 'is-danger': errors.target_head_count }"
                    type="number"
                    min="1"
                    placeholder="e.g. 50"
                    v-model.number="menu.target_head_count">
                </div>
                <p v-if="errors.target_head_count" class="help is-danger">{{ errors.target_head_count[0] }}</p>
                <p class="help">Number of guests this menu will serve</p>
              </div>
            </div>
            <div class="column">
              <div class="field">
                <label class="label">Markup Percentage</label>
                <div class="control has-icons-right">
                  <input
                    class="input"
                    :class="{ 'is-danger': errors.markup_percentage }"
                    type="number"
                    min="0"
                    max="500"
                    step="0.01"
                    placeholder="e.g. 25"
                    v-model.number="menu.markup_percentage">
                  <span class="icon is-small is-right">
                    <i class="mdi mdi-percent"></i>
                  </span>
                </div>
                <p v-if="errors.markup_percentage" class="help is-danger">{{ errors.markup_percentage[0] }}</p>
                <p class="help">Markup percentage for selling price calculation</p>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div class="field">
            <label class="label">Description</label>
            <div class="control">
              <textarea
                class="textarea"
                :class="{ 'is-danger': errors.description }"
                placeholder="Menu description or notes..."
                v-model="menu.description">
              </textarea>
            </div>
            <p v-if="errors.description" class="help is-danger">{{ errors.description[0] }}</p>
          </div>

          <hr>

          <!-- Menu Segments -->
          <div class="field">
            <div class="level">
              <div class="level-left">
                <label class="label">Menu Segments</label>
              </div>

            </div>

            <div v-for="(segment, segmentIndex) in menu.segments" :key="'segment-' + segmentIndex" class="segment-container mb-6">
              <div class="segment-header">
                <div class="field has-addons mb-2 grid grid-cols-1 lg:grid-cols-2 gap-6">
                  <div class="control is-expanded">
                    <input
                      class="input"
                      :class="{ 'is-danger': errors[`segments.${segmentIndex}.name`] }"
                      type="text"
                      placeholder="Segment name"
                      v-model="segment.name">
                  </div>
                  <div class="control">
                    <span class="button is-static">
                      ${{ getSegmentCost(segment).toFixed(2) }} per guest
                    </span>
                    <button
                      v-if="menu.segments.length > 1"
                      class="button is-danger"
                      :class="{ 'is-loading': segment.id && deletingSegments.includes(segment.id) }"
                      :disabled="segment.id && deletingSegments.includes(segment.id)"
                      type="button"
                      @click="removeSegment(segmentIndex)">
                      <span class="icon"><i class="mdi mdi-close"></i></span>
                    </button>
                  </div>
                </div>
                <p v-if="errors[`segments.${segmentIndex}.name`]" class="help is-danger">
                  {{ errors[`segments.${segmentIndex}.name`][0] }}
                </p>
              </div>

              <!-- Segment Items -->
              <div class="segment-items">
                <div v-for="(item, itemIndex) in segment.items" :key="'item-' + itemIndex" class="item-row mb-1">
                  <div class="item-recipe">
                    <div class="control">
                      <Multiselect
                        v-model="item.recipe_id"
                        :options="recipes"
                        label="recipe_name"
                        value-prop="id"
                        placeholder="Search and select recipe..."
                        :searchable="true"
                        :clear-on-select="false"
                        :close-on-select="true"
                        :can-deselect="true"
                        :classes="{
                          container: errors[`segments.${segmentIndex}.items.${itemIndex}.recipe_id`] ? 'multiselect-error' : '',
                          containerActive: 'is-active'
                        }"
                        @change="(value) => onRecipeChange(segmentIndex, itemIndex, value)"
                      />
                    </div>
                    <p v-if="errors[`segments.${segmentIndex}.items.${itemIndex}.recipe_id`]" class="help is-danger is-size-7">
                      {{ errors[`segments.${segmentIndex}.items.${itemIndex}.recipe_id`][0] }}
                    </p>
                  </div>

                  <div class="item-quantity">
                    <div class="control">
                      <input
                        class="input"
                        :class="{ 'is-danger': errors[`segments.${segmentIndex}.items.${itemIndex}.quantity`] }"
                        type="number"
                        step="0.01"
                        min="0.01"
                        placeholder="Qty per guest"
                        v-model.number="item.quantity">
                    </div>
                    <p v-if="errors[`segments.${segmentIndex}.items.${itemIndex}.quantity`]" class="help is-danger is-size-7">
                      {{ errors[`segments.${segmentIndex}.items.${itemIndex}.quantity`][0] }}
                    </p>
                  </div>



                  <div class="item-cost">
                    <span class="button is-static is-small" :class="{ 'is-loading': loadingCosts.has(item.recipe_id) }">
                      <span v-if="!loadingCosts.has(item.recipe_id)">
                        ${{ getItemCost(item).toFixed(2) }} per guest
                      </span>
                    </span>
                  </div>

                  <div class="item-notes">
                    <div class="control">
                      <input
                        class="input is-small"
                        type="text"
                        placeholder="Notes (optional)"
                        v-model="item.notes">
                    </div>
                  </div>

                  <div class="item-remove">
                    <button
                      v-if="segment.items.length > 1"
                      class="button is-small is-danger"
                      :class="{ 'is-loading': item.id && deletingItems.includes(item.id) }"
                      :disabled="item.id && deletingItems.includes(item.id)"
                      type="button"
                      @click="removeItem(segmentIndex, itemIndex)">
                      <span class="icon"><i class="mdi mdi-close"></i></span>
                    </button>
                  </div>
                </div>

                <button class="button is-small add-button" type="button" @click="addItem(segmentIndex)">
                  <span class="icon"><i class="mdi mdi-plus"></i></span>
                  <span>Add Recipe to {{ segment.name }}</span>
                </button>
              </div>
            </div>
            <div class="level-right">
                <button class="button is-small is-success is-light add-button" type="button" @click="addCustomSegment">
                  <span class="icon"><i class="mdi mdi-plus"></i></span>
                  <span>Add Segment</span>
                </button>
              </div>
          </div>

          <hr>

          <!-- Cost Summary -->
          <div class="field">
            <div class="box has-background-light">
              <h4 class="title is-5 mb-3">
                <span class="icon">
                  <i class="mdi mdi-calculator"></i>
                </span>
                Cost Summary
              </h4>

              <div class="table-container">
                <table class="table is-fullwidth is-striped is-hoverable">
                  <thead>
                    <tr>
                      <th>Description</th>
                      <th class="has-text-right">Per Person</th>
                      <th class="has-text-right">Total ({{ menu.target_head_count }} guests)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>
                        <strong>Base Cost</strong>
                        <br>
                        <small class="has-text-grey">Cost of ingredients and recipes</small>
                      </td>
                      <td class="has-text-right">
                        <span class="tag is-light is-medium">
                          ${{ getTotalCost().toFixed(2) }}
                        </span>
                      </td>
                      <td class="has-text-right">
                        <span class="tag is-light is-medium">
                          ${{ (getTotalCost() * menu.target_head_count).toFixed(2) }}
                        </span>
                      </td>
                    </tr>

                    <tr v-if="menu.markup_percentage > 0">
                      <td>
                        <strong>Markup ({{ menu.markup_percentage }}%)</strong>
                        <br>
                        <small class="has-text-grey">Profit margin added to base cost</small>
                      </td>
                      <td class="has-text-right">
                        <span class="tag is-warning is-medium">
                          +${{ getMarkupAmount().toFixed(2) }}
                        </span>
                      </td>
                      <td class="has-text-right">
                        <span class="tag is-warning is-medium">
                          +${{ (getMarkupAmount() * menu.target_head_count).toFixed(2) }}
                        </span>
                      </td>
                    </tr>

                    <tr class="has-background-primary-light">
                      <td>
                        <strong class="has-text-primary">Selling Price</strong>
                        <br>
                        <small class="has-text-grey">Final price to charge customers</small>
                      </td>
                      <td class="has-text-right">
                        <span class="tag is-primary is-medium">
                          <strong>${{ getSellingPricePerPerson().toFixed(2) }}</strong>
                        </span>
                      </td>
                      <td class="has-text-right">
                        <span class="tag is-success is-medium">
                          <strong>${{ (getSellingPricePerPerson() * menu.target_head_count).toFixed(2) }}</strong>
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Additional Info -->
              <div class="level mt-3">
                <div class="level-left">
                  <div class="level-item">
                    <div>
                      <p class="heading">Target Head Count</p>
                      <p class="title is-6">{{ menu.target_head_count }} guests</p>
                    </div>
                  </div>
                  <div class="level-item" v-if="menu.markup_percentage > 0">
                    <div>
                      <p class="heading">Profit Margin</p>
                      <p class="title is-6">{{ menu.markup_percentage }}%</p>
                    </div>
                  </div>
                </div>
                <div class="level-right">
                  <div class="level-item">
                    <div class="has-text-right">
                      <p class="heading">Revenue Potential</p>
                      <p class="title is-5 has-text-success">
                        ${{ (getSellingPricePerPerson() * menu.target_head_count).toFixed(2) }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <hr>

          <!-- Submit Buttons -->
          <div class="field grouped">
            <div class="control">
              <button
                type="submit"
                class="button green"
                :class="{ 'is-loading': isSubmitting }"
                :disabled="isSubmitting">
                {{ isEditMode ? 'Update Menu' : 'Create Menu' }}
              </button>
            </div>
            <div class="control">
              <router-link to="/menus" class="button">
                Cancel
              </router-link>
            </div>
          </div>

        </form>
      </div>
    </div>
  </section>
</template>

<script>
import axios from 'axios';
import { useFlashMessage } from '@/composables/useFlashMessage.js';
import Multiselect from '@vueform/multiselect';

export default {
  components: {
    Multiselect
  },
  setup() {
    const { success, error } = useFlashMessage();
    return { success, error };
  },
  data() {
    return {
      menu: {
        name: '',
        description: '',
        target_head_count: 1,
        markup_percentage: 0,
        segments: [
          {
            name: 'Starter',
            sort_order: 1,
            items: [
              { recipe_id: '', quantity: 1, notes: '' }
            ]
          },
          {
            name: 'Main',
            sort_order: 2,
            items: [
              { recipe_id: '', quantity: 1, notes: '' }
            ]
          },
          {
            name: 'Side',
            sort_order: 3,
            items: [
              { recipe_id: '', quantity: 1, notes: '' }
            ]
          },
          {
            name: 'Dessert',
            sort_order: 4,
            items: [
              { recipe_id: '', quantity: 1, notes: '' }
            ]
          }
        ]
      },
      recipes: [],
      recipeCosts: {}, // Store calculated costs for recipes { recipeId: totalCost }
      loadingCosts: new Set(), // Track which recipe costs are being loaded
      errors: {},
      isSubmitting: false,
      isLoading: true,
      menuId: null,
      deletingSegments: [], // Track which segments are being deleted
      deletingItems: [], // Track which items are being deleted
    };
  },
  computed: {
    isEditMode() {
      return this.$route.params.id !== undefined;
    }
  },
  watch: {
    // Watch for changes in target_head_count to update total costs display
    'menu.target_head_count'() {
      // Total cost calculations will automatically update
      // No need to refetch recipe costs since those don't change
    }
  },
  methods: {
    async fetchRecipes() {
      try {
        const response = await axios.get('/api/recipes-dropdown-data');
        this.recipes = response.data.recipes;
      } catch (fetchError) {
        console.error('Error fetching recipes:', fetchError);
        this.error('Loading Error', 'Error loading recipes. Please refresh the page.');
      }
    },

    async fetchRecipeCosts() {
      try {
        // Get all unique recipe IDs that are selected
        const recipeIds = [];
        this.menu.segments.forEach(segment => {
          segment.items.forEach(item => {
            if (item.recipe_id && !recipeIds.includes(item.recipe_id)) {
              recipeIds.push(item.recipe_id);
            }
          });
        });

        if (recipeIds.length === 0) {
          this.recipeCosts = {};
          return;
        }

        const response = await axios.post('/api/recipes/calculate-multiple-costs', {
          recipe_ids: recipeIds
        });

        if (response.data.success) {
          // Convert the cost data array to an object for easy lookup
          this.recipeCosts = {};
          response.data.cost_data.forEach(cost => {
            this.recipeCosts[cost.recipe_id] = cost.total_cost || 0;
          });
        }
      } catch (error) {
        console.error('Error fetching recipe costs:', error);
        // Don't show error to user, just log it and continue with 0 costs
        this.recipeCosts = {};
      }
    },

    async fetchSingleRecipeCost(recipeId) {
      try {
        // Check if we already have the cost for this recipe or if it's currently loading
        if (this.recipeCosts[recipeId] !== undefined || this.loadingCosts.has(recipeId)) {
          return; // Already have the cost or currently loading
        }

        // Mark as loading
        this.loadingCosts.add(recipeId);

        const response = await axios.get(`/api/recipes/${recipeId}/cost`);

        if (response.data.success) {
          // Store the cost for this recipe
          this.recipeCosts[recipeId] = response.data.cost_data.total_cost || 0;
        }
      } catch (error) {
        console.error(`Error fetching cost for recipe ${recipeId}:`, error);
        // Set cost to 0 if there's an error
        this.recipeCosts[recipeId] = 0;
      } finally {
        // Remove from loading set
        this.loadingCosts.delete(recipeId);
      }
    },

    async fetchMenu() {
      if (!this.isEditMode) return;

      try {
        const response = await axios.get(`/api/menus/${this.$route.params.id}`);
        const menuData = response.data.menu;

        this.menuId = menuData.id;
        this.menu.name = menuData.name;
        this.menu.description = menuData.description || '';
        this.menu.target_head_count = menuData.target_head_count;
        this.menu.markup_percentage = menuData.markup_percentage || 0;

        // Populate segments
        if (menuData.segments && menuData.segments.length > 0) {
          this.menu.segments = menuData.segments.map(segment => ({
            id: segment.id,
            name: segment.name,
            sort_order: segment.sort_order,
            items: segment.items && segment.items.length > 0
              ? segment.items.map(item => ({
                  id: item.id,
                  recipe_id: item.recipe_id,
                  quantity: parseFloat(item.quantity),
                  notes: item.notes || ''
                }))
              : [{ recipe_id: '', quantity: 1, notes: '' }]
          }));
        }

      } catch (fetchError) {
        console.error('Error fetching menu:', fetchError);
        this.error('Loading Error', 'Error loading menu data.');
        this.$router.push('/menus');
      }
    },

    addCustomSegment() {
      const newSegment = {
        name: 'Custom Segment',
        sort_order: this.menu.segments.length + 1,
        items: [
          { recipe_id: '', quantity: 1, notes: '' }
        ]
      };
      this.menu.segments.push(newSegment);
    },

    async removeSegment(segmentIndex) {
      if (this.menu.segments.length <= 1) {
        return; // Don't allow removing the last segment
      }

      const segment = this.menu.segments[segmentIndex];

      // If this is an existing segment (has an ID) and we're in edit mode, delete from backend
      if (this.isEditMode && segment.id) {
        // Add to deleting list
        this.deletingSegments.push(segment.id);

        try {
          await axios.delete(`/api/menu-segments/${segment.id}`);
        } catch (error) {
          console.error('Error deleting segment from backend:', error);
          this.error('Delete Failed', 'Failed to delete segment. Please try again.');
          return; // Don't remove from frontend if backend deletion failed
        } finally {
          // Remove from deleting list
          this.deletingSegments = this.deletingSegments.filter(id => id !== segment.id);
        }
      }

      // Remove from frontend
      this.menu.segments.splice(segmentIndex, 1);
    },

    addItem(segmentIndex) {
      const newItem = {
        recipe_id: '',
        quantity: 1,
        notes: ''
      };
      this.menu.segments[segmentIndex].items.push(newItem);
      // Note: No need to fetch costs here since recipe_id is empty
    },

    async removeItem(segmentIndex, itemIndex) {
      if (this.menu.segments[segmentIndex].items.length <= 1) {
        return; // Don't allow removing the last item from a segment
      }

      const item = this.menu.segments[segmentIndex].items[itemIndex];
      const itemKey = `${segmentIndex}-${itemIndex}`;

      // If this is an existing item (has an ID) and we're in edit mode, delete from backend
      if (this.isEditMode && item.id) {
        // Add to deleting list
        this.deletingItems.push(item.id);

        try {
          await axios.delete(`/api/menu-segment-items/${item.id}`);
        } catch (error) {
          console.error('Error deleting item from backend:', error);
          this.error('Delete Failed', 'Failed to delete item. Please try again.');
          return; // Don't remove from frontend if backend deletion failed
        } finally {
          // Remove from deleting list
          this.deletingItems = this.deletingItems.filter(id => id !== item.id);
        }
      }

      // Remove from frontend
      this.menu.segments[segmentIndex].items.splice(itemIndex, 1);

      // Note: We keep the cost in recipeCosts even after removing item
      // since the same recipe might be used elsewhere in the menu
    },

    async onRecipeChange(segmentIndex, itemIndex, recipeId) {
      // Optional: Auto-set quantity based on recipe servings or other logic
      const selectedRecipe = this.recipes.find(recipe => recipe.id == recipeId);
      if (selectedRecipe) {
        // Could implement logic to suggest quantity based on recipe servings
      }

      // Fetch cost for the selected recipe immediately
      if (recipeId) {
        await this.fetchSingleRecipeCost(recipeId);
      }
    },

    getRecipeCost(recipeId) {
      if (!recipeId) return 0;

      // If we don't have the cost yet, trigger a fetch
      if (this.recipeCosts[recipeId] === undefined) {
        this.fetchSingleRecipeCost(recipeId);
        return 0; // Return 0 while loading
      }

      return this.recipeCosts[recipeId] || 0;
    },

    getItemCost(item) {
      if (!item.recipe_id || !item.quantity) return 0;

      const recipeCost = this.getRecipeCost(item.recipe_id);
      // Always calculate per guest pricing
      return recipeCost * item.quantity;
    },

    getSegmentCost(segment) {
      return segment.items.reduce((total, item) => {
        return total + this.getItemCost(item);
      }, 0);
    },

    getTotalCost() {
      return this.menu.segments.reduce((total, segment) => {
        return total + this.getSegmentCost(segment);
      }, 0);
    },

    getMarkupAmount() {
      if (this.menu.markup_percentage > 0) {
        return this.getTotalCost() * (this.menu.markup_percentage / 100);
      }
      return 0;
    },

    getSellingPricePerPerson() {
      return this.getTotalCost() + this.getMarkupAmount();
    },

    cleanFormData() {
      // Remove empty segments and items
      this.menu.segments = this.menu.segments.filter(segment => {
        segment.items = segment.items.filter(item => item.recipe_id && item.quantity);
        return segment.name && segment.items.length > 0;
      });
    },

    validateMenu() {
      if (this.menu.segments.length === 0) {
        return {
          isValid: false,
          message: 'A menu must have at least one segment with items.'
        };
      }

      const hasItems = this.menu.segments.some(segment =>
        segment.items.some(item => item.recipe_id && item.quantity)
      );

      if (!hasItems) {
        return {
          isValid: false,
          message: 'A menu must have at least one recipe item.'
        };
      }

      return { isValid: true };
    },

    async submitForm() {
      this.errors = {};
      this.isSubmitting = true;

      try {
        // Validate the menu first
        const validation = this.validateMenu();
        if (!validation.isValid) {
          this.error('Validation Error', validation.message);
          return;
        }

        // Clean the form data before submitting
        this.cleanFormData();

        let response;
        if (this.isEditMode) {
          response = await axios.put(`/api/menus/${this.$route.params.id}`, this.menu);
        } else {
          response = await axios.post('/api/menus', this.menu);
        }

        const action = this.isEditMode ? 'updated' : 'created';
        const menuName = response.data.menu.name;

        this.success(
          `Menu ${action === 'created' ? 'Created' : 'Updated'}`,
          `"${menuName}" has been ${action} successfully!`
        );

        // Redirect to menus list with success message
        this.$router.push({
          path: '/menus',
          query: {
            success: action,
            menu: menuName
          }
        });

      } catch (submitError) {
        console.error('Error submitting form:', submitError);

        if (submitError.response && submitError.response.status === 422) {
          // Validation errors
          this.errors = submitError.response.data.errors || {};
        } else {
          // Other errors
          this.error(
            'Submit Failed',
            `Error ${this.isEditMode ? 'updating' : 'creating'} menu. Please try again.`
          );
        }
      } finally {
        this.isSubmitting = false;
      }
    }
  },

  async mounted() {
    this.isLoading = true;
    try {
      await this.fetchRecipes();

      if (this.isEditMode) {
        await this.fetchMenu();
        // Fetch costs for existing menu items
        await this.fetchRecipeCosts();
      }
    } catch (error) {
      console.error('Error loading data:', error);
      this.error('Loading Error', 'Error loading form data. Please refresh the page.');
    } finally {
      this.isLoading = false;
    }
  }
};
</script>

<style scoped>
.segment-container {
  border: 1px solid #e5e5e5;
  border-radius: 6px;
  padding: 1rem;
  background-color: #fafafa;
}

.segment-header {
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #e5e5e5;
}

.segment-items {
  background-color: white;
  padding: 0.1rem;
  border-radius: 4px;
}

.item-row {
  display: grid;
  grid-template-columns: 2fr 0.8fr 1.2fr 1.5fr auto;
  gap: 0.75rem;
  align-items: start;
  padding: 0.5rem;
  border: 1px solid #f0f0f0;
  border-radius: 4px;
  background-color: #fcfcfc;
}

.item-recipe,
.item-quantity,
.item-cost,
.item-notes,
.item-remove {
  display: flex;
  flex-direction: column;
}

.item-remove {
  align-items: center;
  justify-content: flex-start;
  padding-top: 0.375rem;
}

.add-button {
  background-color: #e6f3ff !important;
  border-color: #3273dc !important;
  color: #3273dc !important;
}

.add-button:hover {
  background-color: #d1ecf1 !important;
  border-color: #2366d1 !important;
}

/* Responsive design */
@media (max-width: 768px) {
  .item-row {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }

  .item-remove {
    justify-self: end;
    padding-top: 0;
  }
}

/* Multiselect styling */
:deep(.multiselect) {
  min-height: 2.5rem;
  border-radius: 4px;
  border: 1px solid #dbdbdb;
  background: white;
}

:deep(.multiselect.is-active) {
  border-color: #3273dc;
  box-shadow: 0 0 0 0.125em rgba(50, 115, 220, 0.25);
}

:deep(.multiselect-error) {
  border-color: #ff3860 !important;
}

:deep(.multiselect-error.is-active) {
  box-shadow: 0 0 0 0.125em rgba(255, 56, 96, 0.25) !important;
}
</style>
