<template>
  <div class="image-search-page">
    <main class="main-content">
      <ImageSearchComponent />
    </main>
  </div>
</template>

<script>
import ImageSearchComponent from '../components/search/ImageSearch.vue';

export default {
  name: 'ImageSearchPage',
  components: {
    ImageSearchComponent
  },
  mounted() {
    // SEO
            document.title = 'Recherche par Image - RIKEAA';
    
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
  padding-top: 100px; /* Space for fixed navbar from App.vue */
  padding-left: 1rem;
  padding-right: 1rem;
  min-height: calc(100vh - 100px);
}
</style>