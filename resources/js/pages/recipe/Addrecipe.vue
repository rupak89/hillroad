<template>
  <section class="section main-section">
    <div class="card mb-6">
      <header class="card-header">
        <p class="card-header-title">
          <span class="icon"><i class="mdi mdi-ballot"></i></span>
          Add Recipe
        </p>
      </header>
      <div class="card-content">
        <form method="post">
          <!-- Recipe Name -->
          <div class="field">
            <label class="label">Recipe Name</label>
            <div class="control">
              <input class="input" type="text" placeholder="e.g. Spaghetti Bolognese" v-model="recipe.recipe_name" required>
            </div>
            <p class="help">This field is required</p>
          </div>

          <!-- Instructions -->
          <div class="field">
            <label class="label">Instructions</label>
            <div class="control">
              <textarea class="textarea" placeholder="Describe steps here..." v-model="recipe.instruction"></textarea>
            </div>
          </div>

          <!-- Thumbnail Upload (optional) -->
          <div class="field">
            <label class="label">Thumbnail (URL)</label>
            <div class="control">
              <input class="input" type="text" placeholder="https://example.com/image.jpg" v-model="recipe.thumbnail">
            </div>
          </div>

          <hr>

          <!-- Ingredients (Items) -->
          <div class="field">
            <label class="label">Ingredients</label>
            <div v-for="(ingredient, index) in recipe.ingredients" :key="index" class="columns-3 mb-3">
              <div class="column">
                <div class="select is-fullwidth">
                  <select v-model="ingredient.item_id">
                    <option disabled value="">Select Item</option>
                    <option v-for="item in items" :value="item.id">{{ item.item_name }}</option>
                  </select>
                </div>
              </div>
              <div class="column">
                <input class="input" type="text" placeholder="Unit (e.g. g, ml)" v-model="ingredient.unit">
              </div>
              <div class="column">
                <input class="input" type="number" placeholder="Quantity" v-model="ingredient.quantity">
              </div>
            </div>
            <button class="button is-small is-info" type="button" @click="addIngredient">+ Add Ingredient</button>
          </div>

          <hr>

          <!-- Sub-Recipes -->
          <div class="field">
            <label class="label">Sub-Recipes</label>
            <div v-for="(sub, index) in recipe.sub_recipes" :key="'sub' + index" class="columns-2 mb-3">
              <div class="column">
                <div class="select is-fullwidth">
                  <select v-model="sub.child_recipe_id">
                    <option disabled value="">Select Sub-Recipe</option>
                    <option v-for="r in recipes" :value="r.id">{{ r.recipe_name }}</option>
                  </select>
                </div>
              </div>
              <div class="column">
                <input class="input" type="number" placeholder="Quantity" v-model="sub.quantity">
              </div>
            </div>
            <button class="button is-small is-info" type="button" @click="addSubRecipe">+ Add Sub-Recipe</button>
          </div>

          <hr>

          <!-- Submit Buttons -->
          <div class="field grouped">
            <div class="control">
              <button type="submit" class="button green">
                Add Recipe
              </button>
            </div>
            <div class="control">
              <button type="reset" class="button red" @click.prevent="resetForm">
                Reset
              </button>
            </div>
          </div>

        </form>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  data() {
    return {
      recipe: {
        recipe_name: '',
        instruction: '',
        thumbnail: '',
        ingredients: [
          { item_id: '', unit: '', quantity: '' }
        ],
        sub_recipes: [
          { child_recipe_id: '', quantity: '' }
        ]
      },
      items: [],     // to be fetched from API
      recipes: []    // for sub-recipes dropdown
    };
  },
  methods: {
    addIngredient() {
      this.recipe.ingredients.push({ item_id: '', unit: '', quantity: '' });
    },
    addSubRecipe() {
      this.recipe.sub_recipes.push({ child_recipe_id: '', quantity: '' });
    },
    resetForm() {
      this.recipe = {
        recipe_name: '',
        instruction: '',
        thumbnail: '',
        ingredients: [{ item_id: '', unit: '', quantity: '' }],
        sub_recipes: [{ child_recipe_id: '', quantity: '' }]
      };
    }
  },
  mounted() {
    // Fetch items and recipes here, for example using Axios
    // axios.get('/api/items').then(res => this.items = res.data);
    // axios.get('/api/recipes').then(res => this.recipes = res.data);
  }
};
</script>
