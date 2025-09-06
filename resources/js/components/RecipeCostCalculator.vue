<template>
  <div class="recipe-cost-calculator">
    <div class="field">
      <label class="label">Cost Calculation</label>
      <div class="field">
        <div class="control">
          <label class="label is-small">Quantity</label>
          <input
            class="input"
            type="number"
            v-model="quantity"
            min="1"
            placeholder="Quantity to make"
            style="max-width: 200px;">
        </div>
        <div v-if="isCalculating" class="has-text-info" style="margin-top: 0.5rem;">
          <i class="fas fa-spinner fa-spin"></i> Calculating cost...
        </div>
      </div>
    </div>

    <div v-if="costData" class="cost-results">
      <div class="notification is-light">


        <div class="columns">
          <div class="column">
            <strong>Total Cost: {{ costData.total_cost_formatted }}</strong>
          </div>
          <div class="column" v-if="quantity > 1">
            <strong>Cost per Unit: {{ costData.cost_per_serving_formatted }}</strong>
          </div>
        </div>

        <!-- Show errors if any -->
        <div v-if="costData.breakdown.errors && costData.breakdown.errors.length > 0" class="notification is-warning">
          <h6 class="subtitle is-6">Calculation Warnings:</h6>
          <ul>
            <li v-for="error in costData.breakdown.errors" :key="error">{{ error }}</li>
          </ul>
        </div>

        <!-- Ingredient Costs -->
        <div v-if="costData.breakdown.item_costs.length > 0">
          <h5 class="subtitle is-6">Ingredients:</h5>
          <table class="table is-fullwidth is-striped">
            <thead>
              <tr>
                <th>Item</th>
                <th>Quantity</th>
                <!-- <th>Cost per Unit</th> -->
                <th>Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in costData.breakdown.item_costs" :key="item.item_id">
                <td>{{ item.item_name }}</td>
                <td class="text-right">{{ item.quantity }} {{ item.unit }}</td>
                <!-- <td>{{ item.cost_per_unit || 'N/A' }}</td> -->
                <td>
                  <span v-if="item.can_calculate">
                    ${{ item.total_cost.toFixed(2) }}
                  </span>
                  <span v-else class="has-text-danger">
                    {{ item.error || 'Cannot calculate' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Sub-recipe Costs -->
        <div v-if="costData.breakdown.sub_recipe_costs.length > 0">
          <h5 class="subtitle is-6">Sub-recipes:</h5>
          <table class="table is-fullwidth is-striped">
            <thead>
              <tr>
                <th>Recipe</th>
                <th>Quantity</th>
                <th>Unit Cost</th>
                <th>Total Cost</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="recipe in costData.breakdown.sub_recipe_costs" :key="recipe.recipe_id">
                <td>{{ recipe.recipe_name }}</td>
                <td>{{ recipe.quantity }}</td>
                <td>
                  <span v-if="recipe.can_calculate">
                    ${{ recipe.unit_cost.toFixed(2) }}
                  </span>
                  <span v-else class="has-text-danger">
                    Cannot calculate
                  </span>
                </td>
                <td>
                  <span v-if="recipe.can_calculate">
                    ${{ recipe.total_cost.toFixed(2) }}
                  </span>
                  <span v-else class="has-text-danger">
                    Cannot calculate
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="error" class="notification is-danger">
      <p>{{ error }}</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'RecipeCostCalculator',
  props: {
    recipeId: {
      type: [String, Number],
      required: true
    }
  },
  data() {
    return {
      quantity: 1,
      costData: null,
      isCalculating: false,
      error: null,
      debounceTimer: null
    };
  },
  watch: {
    quantity(newValue, oldValue) {
      if (newValue !== oldValue && newValue > 0) {
        // Debounce the calculation to avoid too many API calls
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
          this.calculateCost();
        }, 500);
      }
    }
  },
  methods: {
    async calculateCost() {
      this.isCalculating = true;
      this.error = null;

      try {
        const response = await axios.post(`/api/recipes/${this.recipeId}/cost-for-quantity`, {
          quantity: this.quantity
        });

        if (response.data.success) {
          this.costData = response.data.cost_data;
        } else {
          this.error = response.data.message || 'Failed to calculate cost';
        }
      } catch (error) {
        console.error('Error calculating cost:', error);
        this.error = error.response?.data?.message || 'Error calculating cost. Please try again.';
      } finally {
        this.isCalculating = false;
      }
    }
  },
  mounted() {
    // Auto-calculate cost when component is mounted
    this.calculateCost();
  },
  beforeUnmount() {
    // Clear the debounce timer when component is destroyed
    if (this.debounceTimer) {
      clearTimeout(this.debounceTimer);
    }
  }
};
</script>

<style scoped>
.cost-results {
  margin-top: 1rem;
}

.table th {
  background-color: #f5f5f5;
}

.notification {
  margin-bottom: 1rem;
}
</style>
