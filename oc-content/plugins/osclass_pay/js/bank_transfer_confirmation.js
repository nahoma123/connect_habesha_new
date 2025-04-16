/**
 * Bank Transfer Confirmation Page JavaScript
 * Enhanced version with improved functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Constants for text display
    const NA_TEXT = document.querySelector('html').getAttribute('data-na-text') || 'N/A';
    const ERROR_TEXT = document.querySelector('html').getAttribute('data-error-text') || 'Error loading';
    
    // Initialize the page
    initPage();
    
    /**
     * Main initialization function
     */
    function initPage() {
        // Initialize progress tracker
        updateProgressTracker();
        
        // Handle LocalStorage data
        handleLocalStorageData();
        
        // Add event listeners
        setupEventListeners();
        
        // Add animation effects
        addAnimationEffects();
    }
    
    /**
     * Update the progress tracker based on transaction status
     */
    function updateProgressTracker() {
        const statusElement = document.querySelector('.osp-status');
        if (!statusElement) return;
        
        const currentStatus = statusElement.textContent.trim().toLowerCase();
        const steps = document.querySelectorAll('.osp-progress-step');
        
        if (currentStatus.includes('pending')) {
            // Mark first step as completed, second as active
            if (steps.length >= 1) steps[0].classList.add('completed');
            if (steps.length >= 2) steps[1].classList.add('active');
        } else if (currentStatus.includes('verified') || currentStatus.includes('approved')) {
            // Mark first two steps as completed, third as active
            if (steps.length >= 1) steps[0].classList.add('completed');
            if (steps.length >= 2) steps[1].classList.add('completed');
            if (steps.length >= 3) steps[2].classList.add('active');
        } else if (currentStatus.includes('rejected')) {
            // Mark first step as completed, show error state
            if (steps.length >= 1) steps[0].classList.add('completed');
            if (steps.length >= 2) steps[1].classList.add('rejected');
        }
    }
    
    /**
     * Parse custom extra string from URL parameters
     * @param {string} extraString - The encoded extra string to parse
     * @return {Object} - Parsed key-value pairs
     */
    function parseCustomExtraString(extraString) {
        const result = {};
        if (!extraString) return result;
        
        try {
            // 1. Decode percent encodings first (e.g., %2C -> ',', %27 -> ')
            let decoded = decodeURIComponent(extraString);
            
            // 2. Replace '+' signs with spaces
            decoded = decoded.replace(/\+/g, ' ');
            
            // 3. Split into pairs and process
            const pairs = decoded.split('|');
            pairs.forEach(pair => {
                const parts = pair.split(',', 2); // Split into max 2 parts
                if (parts.length === 2) {
                    const key = parts[0].trim();
                    const value = parts[1].trim();
                    if (key) { // Ensure key is not empty
                        result[key] = value;
                    }
                }
            });
        } catch (e) {
            console.error("Error parsing custom extra string:", extraString, e);
        }
        
        return result;
    }
    
    /**
     * Handle LocalStorage data retrieval and display
     */
    function handleLocalStorageData() {
        try {
            const transactionIdElement = document.getElementById('transaction-id');
            if (!transactionIdElement) {
                console.warn('Transaction ID element not found in the DOM.');
                setAllPlaceholdersToNA();
                return;
            }
            
            const transactionId = transactionIdElement.textContent.trim();
            if (!transactionId) {
                console.warn('Transaction ID is empty.');
                setAllPlaceholdersToNA();
                return;
            }
            
            if (typeof(Storage) === "undefined") {
                console.error("LocalStorage not supported by this browser.");
                setAllPlaceholdersToError();
                return;
            }
            
            const storageKey = `osp_transfer_data_${transactionId}`;
            const storedDataJson = localStorage.getItem(storageKey);
            
            console.log(`LocalStorage Retrieve Attempt: Key='${storageKey}', Raw Data='${storedDataJson}'`);
            
            let storedData = null;
            if (storedDataJson) {
                try {
                    storedData = JSON.parse(storedDataJson);
                    console.log("LocalStorage Retrieve Success: Parsed Data=", storedData);
                } catch (jsonError) {
                    console.error("LocalStorage Retrieve Failed: Could not parse JSON.", jsonError, "Raw Data:", storedDataJson);
                    storedData = null;
                }
            } else {
                console.warn("LocalStorage Retrieve: No data found for key:", storageKey);
            }
            
            // Get element references
            const elements = {
                desc: document.getElementById('display-original-desc'),
                product: document.getElementById('display-original-product'),
                user: document.getElementById('display-extra-user'),
                productName: document.getElementById('display-extra-product-name')
            };
            
            // Check if elements exist
            Object.entries(elements).forEach(([key, element]) => {
                if (!element) console.error(`HTML Element '${key}' not found!`);
            });
            
            // Populate placeholders if data exists
            if (storedData) {
                populatePlaceholders(elements, storedData);
                
                // Clean up LocalStorage
                localStorage.removeItem(storageKey);
                console.log('LocalStorage Cleanup: Removed data for key:', storageKey);
            } else {
                setAllPlaceholdersToNA();
            }
        } catch (e) {
            console.error("LocalStorage Retrieve Failed: Unhandled JavaScript error.", e);
            setAllPlaceholdersToError();
        }
    }
    
    /**
     * Populate placeholders with data from localStorage
     * @param {Object} elements - DOM elements to populate
     * @param {Object} storedData - Data retrieved from localStorage
     */
    function populatePlaceholders(elements, storedData) {
        // Description
        if (elements.desc) {
            if (storedData.hasOwnProperty('desc')) {
                elements.desc.textContent = storedData.desc !== null && storedData.desc !== undefined ? storedData.desc : NA_TEXT;
                console.log("Setting desc:", elements.desc.textContent);
            } else {
                elements.desc.textContent = NA_TEXT;
                console.log("Setting desc: N/A (property missing)");
            }
        }
        
        // Product Code
        if (elements.product) {
            if (storedData.hasOwnProperty('product') && storedData.product !== null && storedData.product !== undefined) {
                elements.product.textContent = storedData.product;
                console.log("Setting product:", elements.product.textContent);
            } else {
                elements.product.textContent = NA_TEXT;
                console.log("Setting product: N/A (property missing or invalid)");
            }
        }
        
        // Extra Data (User Name and Product Name)
        if (storedData.hasOwnProperty('extra') && storedData.extra) {
            const extraData = parseCustomExtraString(storedData.extra);
            console.log("Parsed Extra Data:", extraData);
            
            // User Name
            if (elements.user) {
                if (extraData.hasOwnProperty('name') && extraData.name !== null && extraData.name !== undefined) {
                    elements.user.textContent = extraData.name;
                    console.log("Setting user (from extra):", elements.user.textContent);
                } else {
                    elements.user.textContent = NA_TEXT + ' (name not found)';
                    console.log("Setting user: N/A (name key missing or invalid in parsed extra)");
                }
            }
            
            // Product Name
            if (elements.productName) {
                if (extraData.hasOwnProperty('product_name') && extraData.product_name !== null && extraData.product_name !== undefined) {
                    elements.productName.textContent = extraData.product_name;
                    console.log("Setting product name (from extra):", elements.productName.textContent);
                } else {
                    elements.productName.textContent = NA_TEXT + ' (product_name not found)';
                    console.log("Setting product name: N/A (product_name key missing or invalid in parsed extra)");
                }
            }
        } else {
            // Handle case where 'extra' property itself is missing
            if (elements.user) {
                elements.user.textContent = NA_TEXT + ' (extra missing)';
                console.log("Setting user: N/A (extra property missing or empty)");
            }
            
            if (elements.productName) {
                elements.productName.textContent = NA_TEXT + ' (extra missing)';
                console.log("Setting product name: N/A (extra property missing or empty)");
            }
        }
    }
    
    /**
     * Set all placeholders to N/A
     */
    function setAllPlaceholdersToNA() {
        console.log("Setting all localStorage fields to N/A because no valid data was retrieved.");
        const descEl = document.getElementById('display-original-desc');
        const prodEl = document.getElementById('display-original-product');
        const userEl = document.getElementById('display-extra-user');
        const prodNameEl = document.getElementById('display-extra-product-name');
        
        if (descEl) descEl.textContent = NA_TEXT;
        if (prodEl) prodEl.textContent = NA_TEXT;
        if (userEl) userEl.textContent = NA_TEXT;
        if (prodNameEl) prodNameEl.textContent = NA_TEXT;
    }
    
    /**
     * Set all placeholders to Error
     */
    function setAllPlaceholdersToError() {
        console.error("Setting all placeholders to Error text due to critical failure.");
        const descEl = document.getElementById('display-original-desc');
        const prodEl = document.getElementById('display-original-product');
        const userEl = document.getElementById('display-extra-user');
        const prodNameEl = document.getElementById('display-extra-product-name');
        
        if (descEl) descEl.textContent = ERROR_TEXT;
        if (prodEl) prodEl.textContent = ERROR_TEXT;
        if (userEl) userEl.textContent = ERROR_TEXT;
        if (prodNameEl) prodNameEl.textContent = ERROR_TEXT;
    }
    
    /**
     * Setup event listeners for interactive elements
     */
    function setupEventListeners() {
        // Support button click handler
        const supportBtn = document.querySelector('.osp-support-btn');
        if (supportBtn) {
            supportBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const supportSection = document.querySelector('.osp-support-form');
                if (supportSection) {
                    supportSection.classList.toggle('osp-support-form-visible');
                }
            });
        }
        
        // Continue button hover effect
        const continueBtn = document.querySelector('.osp-continue-btn');
        if (continueBtn) {
            continueBtn.addEventListener('mouseenter', function() {
                this.classList.add('osp-btn-hover');
            });
            continueBtn.addEventListener('mouseleave', function() {
                this.classList.remove('osp-btn-hover');
            });
        }
    }
    
    /**
     * Add animation effects to page elements
     */
    function addAnimationEffects() {
        // Add fade-in effect to main container
        const container = document.querySelector('.osp-container');
        if (container) {
            container.classList.add('osp-fade-in');
        }
        
        // Add staggered fade-in to list items
        const listItems = document.querySelectorAll('.osp-summary-list li');
        listItems.forEach((item, index) => {
            setTimeout(() => {
                item.classList.add('osp-item-visible');
            }, 100 * index);
        });
    }
});