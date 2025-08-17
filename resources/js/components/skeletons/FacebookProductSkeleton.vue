<template>
  <div class="bg-white rounded-lg border border-gray-200 overflow-hidden animate-pulse">
    <!-- User Header Skeleton -->
    <div class="flex items-center p-3 border-b border-gray-100">
      <div class="w-10 h-10 bg-gray-300 rounded-full mr-3"></div>
      <div class="flex-1">
        <div class="h-4 bg-gray-300 rounded w-24 mb-2"></div>
        <div class="h-3 bg-gray-300 rounded w-16"></div>
      </div>
      <div class="w-5 h-5 bg-gray-300 rounded"></div>
    </div>

    <!-- Product Image Skeleton -->
    <div class="h-64 bg-gray-300"></div>

    <!-- Product Info Skeleton -->
    <div class="p-3">
      <div class="h-5 bg-gray-300 rounded w-3/4 mb-2"></div>
      <div class="h-4 bg-gray-300 rounded w-full mb-2"></div>
      <div class="h-4 bg-gray-300 rounded w-1/2 mb-3"></div>

      <!-- Product Details Skeleton -->
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center space-x-4">
          <div class="h-4 bg-gray-300 rounded w-20"></div>
          <div class="h-4 bg-gray-300 rounded w-16"></div>
        </div>
        <div class="h-3 bg-gray-300 rounded w-12"></div>
      </div>

      <!-- Action Buttons Skeleton -->
      <div class="flex items-center justify-between pt-3 border-t border-gray-100">
        <div class="flex items-center space-x-6">
          <div class="flex items-center space-x-2">
            <div class="w-5 h-5 bg-gray-300 rounded"></div>
            <div class="h-4 bg-gray-300 rounded w-8"></div>
          </div>
          <div class="flex items-center space-x-2">
            <div class="w-5 h-5 bg-gray-300 rounded"></div>
            <div class="h-4 bg-gray-300 rounded w-8"></div>
          </div>
          <div class="flex items-center space-x-2">
            <div class="w-5 h-5 bg-gray-300 rounded"></div>
            <div class="h-4 bg-gray-300 rounded w-16"></div>
          </div>
        </div>
        <div class="w-16 h-8 bg-gray-300 rounded-lg"></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FacebookProductSkeleton'
}
</script>
