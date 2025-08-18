<template>
  <div class="bg-white rounded-lg border border-gray-200 overflow-hidden animate-pulse">
    <!-- Image Skeleton -->
    <div class="aspect-square bg-gray-200"></div>
    
    <!-- Content Skeleton -->
    <div class="p-2.5 sm:p-3 lg:p-4">
      <!-- Title and Price -->
      <div class="flex items-start justify-between mb-1 sm:mb-1.5 lg:mb-2">
        <div class="flex-1">
          <div class="h-2.5 sm:h-3 lg:h-4 bg-gray-200 rounded w-3/4 mb-1 sm:mb-1.5 lg:mb-2"></div>
          <div class="h-2 sm:h-2.5 lg:h-3 bg-gray-200 rounded w-1/2"></div>
        </div>
        <div class="ml-1.5 sm:ml-2">
          <div class="h-3 sm:h-4 lg:h-5 bg-gray-200 rounded w-16"></div>
        </div>
      </div>
      
      <!-- Details -->
      <div class="space-y-0.5 sm:space-y-1 mb-1.5 sm:mb-2 lg:mb-3">
        <div class="h-2 sm:h-2.5 lg:h-3 bg-gray-200 rounded w-1/3"></div>
        <div class="h-2 sm:h-2.5 lg:h-3 bg-gray-200 rounded w-1/4"></div>
      </div>
      
      <!-- User info and stats -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-1 sm:space-x-1.5 lg:space-x-2">
          <div class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 bg-gray-300 rounded-full"></div>
          <div class="h-2 sm:h-2.5 lg:h-3 bg-gray-300 rounded w-16"></div>
        </div>
        <div class="flex items-center space-x-1.5 sm:space-x-2 lg:space-x-3">
          <div class="h-2 sm:h-2.5 lg:h-3 bg-gray-300 rounded w-8"></div>
          <div class="h-2 sm:h-2.5 lg:h-3 bg-gray-300 rounded w-8"></div>
        </div>
      </div>
    </div>
  </div>
</template>



