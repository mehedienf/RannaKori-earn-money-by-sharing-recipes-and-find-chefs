// ============================================
// Ranna-Kori - Main JavaScript File
// ============================================

document.addEventListener('DOMContentLoaded', function() {
  initializeApp();
});

// Initialize the application
function initializeApp() {
  setupEventListeners();
  setupTabNavigation();
  setupRatingSystem();
  setupFormHandlers();
}

// ============================================
// Event Listeners Setup
// ============================================

function setupEventListeners() {
  // Like button functionality
  const likeButtons = document.querySelectorAll('.btn-like');
  likeButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      this.classList.toggle('liked');
      updateLikeCount(this);
    });
  });

  // Search functionality
  const searchInput = document.querySelector('.search-box input');
  if (searchInput) {
    searchInput.addEventListener('keyup', debounce(function(e) {
      const searchTerm = e.target.value.toLowerCase();
      if (searchTerm.length > 0) {
        console.log('Searching for:', searchTerm);
        // Add search functionality here
      }
    }, 300));
  }

  // View Recipe buttons
  const viewButtons = document.querySelectorAll('.btn-view');
  viewButtons.forEach(btn => {
    if (btn.textContent === 'View Recipe') {
      btn.addEventListener('click', function() {
        window.location.href = 'pages/recipe-detail.html';
      });
    }
  });

  // Add ingredient button
  const addIngredientBtn = document.querySelector('[data-action="add-ingredient"]');
  if (addIngredientBtn) {
    addIngredientBtn.addEventListener('click', addIngredientField);
  }

  // Add instruction button
  const addInstructionBtn = document.querySelector('[data-action="add-instruction"]');
  if (addInstructionBtn) {
    addInstructionBtn.addEventListener('click', addInstructionField);
  }
}

// ============================================
// Tab Navigation
// ============================================

function setupTabNavigation() {
  const tabButtons = document.querySelectorAll('.tab-btn');
  tabButtons.forEach(btn => {
    btn.addEventListener('click', function() {
      // Remove active class from all tabs
      tabButtons.forEach(b => {
        b.classList.remove('active');
        b.style.color = 'var(--text-light)';
        b.style.borderBottom = 'none';
      });

      // Add active class to clicked tab
      this.classList.add('active');
      this.style.color = 'var(--primary-color)';
      this.style.borderBottom = '3px solid var(--primary-color)';
      this.style.paddingBottom = '10px';

      // Switch tab content
      const tabContent = this.getAttribute('data-tab');
      switchTabContent(tabContent);
    });
  });
}

function switchTabContent(tabName) {
  const tabContents = document.querySelectorAll('.tab-content');
  tabContents.forEach(content => {
    content.style.display = 'none';
  });

  const activeContent = document.querySelector(`[data-content="${tabName}"]`);
  if (activeContent) {
    activeContent.style.display = 'block';
  }
}

// ============================================
// Rating System
// ============================================

function setupRatingSystem() {
  const ratingStars = document.querySelectorAll('.rating-star');
  ratingStars.forEach(star => {
    star.addEventListener('click', function() {
      const rating = this.getAttribute('data-rating');
      setRating(rating);
    });

    star.addEventListener('mouseover', function() {
      const rating = this.getAttribute('data-rating');
      highlightStars(rating);
    });
  });

  // Reset on mouse leave
  const ratingContainer = document.querySelector('[style*="flex: gap: 10px; font-size: 28px"]');
  if (ratingContainer) {
    ratingContainer.addEventListener('mouseleave', function() {
      resetStars();
    });
  }
}

function setRating(rating) {
  const stars = document.querySelectorAll('.rating-star');
  stars.forEach((star, index) => {
    if (index < rating) {
      star.textContent = '★';
      star.style.color = 'var(--rating-color)';
    } else {
      star.textContent = '☆';
      star.style.color = 'inherit';
    }
  });
}

function highlightStars(rating) {
  const stars = document.querySelectorAll('.rating-star');
  stars.forEach((star, index) => {
    if (index < rating) {
      star.textContent = '★';
    } else {
      star.textContent = '☆';
    }
  });
}

function resetStars() {
  const stars = document.querySelectorAll('.rating-star');
  stars.forEach(star => {
    star.textContent = '☆';
  });
}

// ============================================
// Form Handlers
// ============================================

function setupFormHandlers() {
  const forms = document.querySelectorAll('form');
  forms.forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      handleFormSubmit(this);
    });
  });

  // File upload drag and drop
  const dropZones = document.querySelectorAll('[style*="border: 2px dashed"]');
  dropZones.forEach(zone => {
    zone.addEventListener('dragover', (e) => {
      e.preventDefault();
      zone.style.backgroundColor = 'rgba(211, 47, 47, 0.05)';
    });

    zone.addEventListener('dragleave', () => {
      zone.style.backgroundColor = 'transparent';
    });

    zone.addEventListener('drop', (e) => {
      e.preventDefault();
      zone.style.backgroundColor = 'transparent';
      console.log('Files dropped:', e.dataTransfer.files);
    });
  });
}

function handleFormSubmit(form) {
  const formData = new FormData(form);
  console.log('Form submitted:', Object.fromEntries(formData));

  // Show success message
  const successMsg = form.querySelector('.success-message');
  if (successMsg) {
    successMsg.classList.remove('hidden');
    setTimeout(() => {
      successMsg.classList.add('hidden');
    }, 5000);
  }

  // Reset form
  setTimeout(() => {
    form.reset();
  }, 1000);
}

// ============================================
// Ingredient & Instruction Management
// ============================================

function addIngredientField() {
  const container = document.getElementById('ingredients-container');
  if (container) {
    const newField = document.createElement('div');
    newField.style.display = 'grid';
    newField.style.gridTemplateColumns = '2fr 1fr 100px';
    newField.style.gap = '10px';
    newField.style.marginBottom = '10px';
    newField.innerHTML = `
      <input type="text" placeholder="Ingredient name" required>
      <input type="text" placeholder="Quantity (e.g., 500g)" required>
      <button type="button" class="btn-like" style="padding: 8px; background: #ffebee; color: var(--primary-color);">Remove</button>
    `;
    container.appendChild(newField);

    // Add remove functionality
    newField.querySelector('button').addEventListener('click', function() {
      newField.remove();
    });
  }
}

function addInstructionField() {
  const container = document.getElementById('instructions-container');
  if (container) {
    const stepNumber = container.querySelectorAll('> div').length + 1;
    const newStep = document.createElement('div');
    newStep.style.marginBottom = '15px';
    newStep.style.paddingBottom = '15px';
    newStep.style.borderBottom = '1px solid var(--border-color)';
    newStep.innerHTML = `
      <label style="display: flex; align-items: center; margin-bottom: 10px;">
        <span style="background: var(--primary-color); color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-weight: bold;">${stepNumber}</span>
        <span style="color: var(--text-dark); font-weight: 600;">Step ${stepNumber}</span>
      </label>
      <textarea placeholder="Describe step ${stepNumber}..." style="margin-bottom: 10px;"></textarea>
      <button type="button" class="btn-like" style="padding: 8px 12px; background: #ffebee; color: var(--primary-color); font-size: 12px;">Remove Step</button>
    `;
    container.appendChild(newStep);

    // Add remove functionality
    newStep.querySelector('button').addEventListener('click', function() {
      newStep.remove();
    });
  }
}

// ============================================
// Like Functionality
// ============================================

function updateLikeCount(button) {
  const likeText = button.textContent;
  const parts = likeText.split(' ');
  let count = parseInt(parts[1]) || 0;

  if (button.classList.contains('liked')) {
    count++;
  } else {
    count--;
  }

  button.textContent = `❤️ ${count}`;
}

// ============================================
// Utility Functions
// ============================================

function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Scroll to top functionality
function scrollToTop() {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
}

// Show notification
function showNotification(message, type = 'success') {
  const notification = document.createElement('div');
  notification.className = type === 'success' ? 'success-message' : 'error-message';
  notification.textContent = message;
  notification.style.position = 'fixed';
  notification.style.top = '20px';
  notification.style.right = '20px';
  notification.style.zIndex = '9999';
  notification.style.maxWidth = '400px';

  document.body.appendChild(notification);

  setTimeout(() => {
    notification.remove();
  }, 5000);
}

// Format date
function formatDate(date) {
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(date).toLocaleDateString('en-US', options);
}

// Get user from localStorage
function getCurrentUser() {
  const user = localStorage.getItem('currentUser');
  return user ? JSON.parse(user) : null;
}

// Save user to localStorage
function saveUser(user) {
  localStorage.setItem('currentUser', JSON.stringify(user));
}

// ============================================
// API Simulation (Replace with real API calls)
// ============================================

// Simulate API call for creating recipe
async function createRecipe(recipeData) {
  return new Promise((resolve, reject) => {
    setTimeout(() => {
      console.log('Recipe created:', recipeData);
      resolve({
        success: true,
        message: 'Recipe created successfully',
        points: 50
      });
    }, 1000);
  });
}

// Simulate API call for submitting review
async function submitReview(recipeId, reviewData) {
  return new Promise((resolve, reject) => {
    setTimeout(() => {
      console.log('Review submitted:', reviewData);
      resolve({
        success: true,
        message: 'Review posted successfully',
        points: 10
      });
    }, 1000);
  });
}

// Simulate API call for liking recipe
async function likeRecipe(recipeId) {
  return new Promise((resolve, reject) => {
    setTimeout(() => {
      console.log('Recipe liked:', recipeId);
      resolve({
        success: true,
        points: 1
      });
    }, 500);
  });
}

// ============================================
// Mobile Menu (if needed)
// ============================================

function setupMobileMenu() {
  const menuButton = document.querySelector('.mobile-menu-btn');
  const navMenu = document.querySelector('nav ul');

  if (menuButton && navMenu) {
    menuButton.addEventListener('click', function() {
      navMenu.classList.toggle('active');
    });
  }
}

// Initialize mobile menu
setupMobileMenu();

console.log('Ranna-Kori Frontend Initialized!');
