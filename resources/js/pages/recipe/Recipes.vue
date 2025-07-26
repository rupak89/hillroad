<template>


  <section class="section main-section">

    <section class="is-hero-bar">
        <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
            <h1 class="title">
            Recipes
            </h1>
            <router-link to="/recipes/add" class="button light">
                <button class="button light">Add Recipe</button>
            </router-link>
        </div>
    </section>


    <div class="card has-table">
      <header class="card-header">
        <p class="card-header-title">
          <span class="icon"><i class="mdi mdi-account-multiple"></i></span>
          Items
        </p>
        <a href="#" class="card-header-icon">
          <span class="icon"><i class="mdi mdi-reload"></i></span>
        </a>
      </header>

      <div class="card-content">
        <table>
  <thead>
    <tr>
      <th class="checkbox-cell">
        <label class="checkbox"><input type="checkbox"><span class="check"></span></label>
      </th>
      <th>Thumbnail</th>
      <th>Recipe Name</th>
      <th>Instructions</th>
      <th>Created At</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td class="checkbox-cell">
        <label class="checkbox"><input type="checkbox"><span class="check"></span></label>
      </td>
      <td class="image-cell">
        <div class="image">
          <img src="" class="rounded-full" alt="Recipe Thumbnail">
        </div>
      </td>
      <td data-label="Recipe Name">Spaghetti Bolognese</td>
      <td data-label="Instructions">Boil pasta. Prepare sauce with beef and tomatoes...</td>
      <td data-label="Created At">
        <small class="text-gray-500">Jun 9, 2025</small>
      </td>
      <td class="actions-cell">
        <div class="buttons right nowrap">
          <button class="button small blue --jb-modal" data-target="view-recipe-modal" type="button">
            <span class="icon"><i class="mdi mdi-eye"></i></span>
          </button>
          <button class="button small green --jb-modal" data-target="view-recipe-modal" type="button">
            <span class="icon"><i class="mdi mdi-pencil"></i></span>
          </button>
          <button class="button small red --jb-modal" data-target="delete-recipe-modal" type="button">
            <span class="icon"><i class="mdi mdi-trash-can"></i></span>
          </button>
        </div>
      </td>
    </tr>
  </tbody>
</table>

        <div class="table-pagination">
          <div class="flex items-center justify-between">
            <div class="buttons">
              <button type="button" class="button active">1</button>
              <button type="button" class="button">2</button>
              <button type="button" class="button">3</button>
            </div>
            <small>Page 1 of 3</small>
          </div>
        </div>
      </div>
    </div>
  </section>



  <section class="section main-section">

    <div class="card empty hidden">
      <div class="card-content">
        <div>
          <span class="icon large"><i class="mdi mdi-emoticon-sad mdi-48px"></i></span>
        </div>
        <p>Nothing's here…</p>
      </div>
    </div>
  </section>



</template>
