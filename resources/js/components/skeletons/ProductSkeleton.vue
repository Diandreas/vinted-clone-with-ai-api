<template>
  <div class="bg-white rounded-lg border border-gray-200 overflow-hidden animate-pulse">
    <!-- Image Skeleton -->
    <div class="aspect-square bg-gray-200"></div>
    
    <!-- Content Skeleton -->
    <div class="p-4">
      <!-- Title and Price -->
      <div class="flex items-start justify-between mb-2">
        <div class="flex-1">
          <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
          <div class="h-3 bg-gray-200 rounded w-1/2"></div>
        </div>
        <div class="ml-2">
          <div class="h-5 bg-gray-200 rounded w-16"></div>
        </div>
      </div>
      
      <!-- Details -->
      <div class="space-y-1 mb-3">
        <div class="h-3 bg-gray-200 rounded w-1/3"></div>
        <div class="h-3 bg-gray-200 rounded w-1/4"></div>
      </div>
      
      <!-- User info and stats -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <div class="w-6 h-6 bg-gray-200 rounded-full"></div>
          <div class="h-3 bg-gray-200 rounded w-16"></div>
        </div>
        <div class="flex items-center space-x-3">
          <div class="h-3 bg-gray-200 rounded w-8"></div>
          <div class="h-3 bg-gray-200 rounded w-8"></div>
        </div>
      </div>
    </div>
  </div>
</template>



