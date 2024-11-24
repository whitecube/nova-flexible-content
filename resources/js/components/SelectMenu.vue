<template>
    <div>
        <!-- Add Widget Button -->
        <button 
            type="button"
            class="btn btn-default btn-primary flex items-center justify-center px-4 py-2 rounded-lg font-semibold transition-colors duration-200 bg-primary-500 text-white hover:bg-primary-600"
            @click.prevent="openModal"
        >
            Add Widget
        </button>

        <div class="fixed top-0 left-0 w-screen h-screen z-50 flex items-center justify-center bg-black/80 overflow-hidden"
            v-if="showModal"
            @modal-close="showModal = false"
        >
            <div class="bg-white rounded-lg shadow-lg overflow-hidden w-[1200px] max-h-[90vh] flex flex-col" >
                <!-- Header - Fixed -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold">Select a Widget</h3>
                        <button 
                            type="button"
                            class="text-gray-500 hover:text-gray-700"
                            @click="showModal = false"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Search and Category Filter -->
                    <div class="flex gap-4">
                        <!-- Search Bar -->
                        <div class="relative flex-1">
                            <input 
                                type="text" 
                                v-model="searchQuery"
                                placeholder="Search widgets..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            >
                            <svg 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" 
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <!-- Category Filter -->
                        <select 
                            v-model="selectedCategory"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        >
                            <option value="">All Categories</option>
                            <option v-for="category in categories" :key="category" :value="category">
                                {{ category }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Content - Scrollable -->
                <div class="flex-1 overflow-y-auto p-6">
                    <div v-for="(widgets, category) in groupedAndFilteredLayouts" 
                         :key="category" 
                         class="mb-8"
                    >
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">{{ category }}</h3>
                        <div class="widget-grid">
                            <div
                                v-for="layout in widgets"
                                :key="layout.name"
                                class="widget-item"
                                @click="selectLayout(layout)"
                            > 
                                <div class="widget-item__preview">
                                    <img 
                                        :src="layout.metadata['preview'] || '/images/widgets/default-preview.png'" 
                                        :alt="layout.title"
                                        class="w-full h-full object-contain"
                                    >
                                </div>
                                <div class="widget-item__content">
                                    <h4 class="widget-item__title">{{ layout.title }}</h4>
                                    <p v-if="layout.description" class="widget-item__description">
                                        {{ layout.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- No Results Message -->
                    <div v-if="Object.keys(groupedAndFilteredLayouts).length === 0" class="text-center py-8 text-gray-500">
                        No widgets found matching your search.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        layouts: {
            type: Array,
            required: true
        }
    },

    data() {
        return { 
            showModal: false,
            searchQuery: '',
            selectedCategory: ''
        }
    },

    computed: {
        // Get unique categories from layouts
        categories() {
            return [...new Set(this.layouts.map(layout => layout.metadata['category'] || 'Uncategorized'))].sort();
        },

        // Group and filter layouts by category
        groupedAndFilteredLayouts() {
            const filtered = this.layouts.filter(layout => {
                const matchesSearch = !this.searchQuery || 
                    layout.title.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                    (layout.description && layout.description.toLowerCase().includes(this.searchQuery.toLowerCase()));
                
                const category = layout.metadata['category'] || 'Uncategorized';
                const matchesCategory = !this.selectedCategory || category === this.selectedCategory;

                return matchesSearch && matchesCategory;
            });

            return filtered.reduce((groups, layout) => {
                const category = layout.metadata['category'] || 'Uncategorized';
                if (!groups[category]) {
                    groups[category] = [];
                }
                groups[category].push(layout);
                return groups;
            }, {});
        }
    },

    methods: {
        openModal() {
            console.log('Opening modal');
            this.showModal = true;
            this.searchQuery = ''; // Reset search when opening modal
            this.selectedCategory = ''; // Reset category filter
        },

        selectLayout(layout) {
            if (!layout) return;
            this.$emit('addGroup', layout);
            this.showModal = false;
            this.searchQuery = ''; // Reset search when closing modal
            this.selectedCategory = ''; // Reset category filter
        }
    }
}
</script>

<style scoped>
@tailwind base;
@tailwind components;
@tailwind utilities;
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background-color: var(--primary);
    color: white;
    border: none;
}

.btn-primary:hover {
    background-color: var(--primary-dark);
}

.widget-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    transition: all 0.3s ease;
}

.widget-item {
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
}

.widget-item:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.widget-item__preview {
    height: 180px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.widget-item__preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.widget-item__content {
    padding: 1rem;
}

.widget-item__title {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}

.widget-item__description {
    font-size: 0.875rem;
    color: #6b7280;
    line-height: 1.4;
}

/* Add styles for search input focus state */
input:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.2);
}
</style>
