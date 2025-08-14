<template>
  <div class="image-search-page">
    <NavBar />
    
    <main class="main-content">
      <ImageSearchComponent />
    </main>
  </div>
</template>

<script>
import NavBar from '../components/layout/NavBar.vue';
import ImageSearchComponent from '../components/search/ImageSearch.vue';

export default {
  name: 'ImageSearchPage',
  components: {
    NavBar,
    ImageSearchComponent
  },
  mounted() {
    // SEO
    document.title = 'Recherche par Image - Vinted Clone';
    
    // Meta description
    const metaDescription = document.querySelector('meta[name="description"]');
    if (metaDescription) {
      metaDescription.setAttribute('content', 
        'Trouvez des produits similaires en uploadant une photo. Notre IA analyse votre image et trouve les articles les plus ressemblants.'
      );
    }
  }
};
</script>

<style scoped>
.image-search-page {
  min-height: 100vh;
  background-color: #f8fafc;
}

.main-content {
  padding-top: 80px; /* Space for fixed navbar */
  min-height: calc(100vh - 80px);
}
</style>