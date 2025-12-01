# Ranna-Kori Frontend Structure

A complete HTML/CSS frontend for the Ranna-Kori Bengali Recipe Sharing Platform.

## Project Structure

```
frontend/
├── index.html              # Home page
├── css/
│   └── style.css          # Main stylesheet with all styling
├── js/
│   └── main.js            # Main JavaScript functionality
├── pages/
│   ├── profile.html       # User profile page
│   ├── create-recipe.html # Create new recipe page
│   ├── recipe-detail.html # Single recipe detail page
│   └── leaderboard.html   # Leaderboard and rankings page
└── images/                # Images folder (for recipe images, etc.)
```

## Files Overview

### 1. **index.html** - Home Page
- Navigation header with search
- Hero section with call-to-action
- Featured recipes grid (6 recipes)
- Recipe categories section
- Points and rewards information
- Footer

### 2. **css/style.css** - Main Stylesheet
- Complete responsive design
- CSS variables for theme colors
- Component styles:
  - Header & Navigation
  - Hero section
  - Recipe cards
  - Category cards
  - Forms
  - Sidebar
  - Profile sections
  - Review components
  - Footer
- Mobile responsive breakpoints

### 3. **js/main.js** - JavaScript Functionality
- Event listener setup
- Like/Unlike functionality
- Rating system
- Form handling
- Tab navigation
- Ingredient/Instruction management
- API simulation functions
- Utility functions

### 4. **pages/profile.html** - User Profile Page
- User profile header with avatar
- Statistics (recipes, points, likes, earnings)
- My Recipes section with cards
- Sidebar with:
  - Earnings display
  - Points information
  - Recent activity

### 5. **pages/create-recipe.html** - Create Recipe Page
- Comprehensive form for creating recipes:
  - Recipe title and description
  - Category selection
  - Servings and cooking time
  - Estimated cost
  - Ingredients management (add/remove)
  - Cooking instructions (step-by-step)
  - Image upload with drag & drop
  - Publish/Save as Draft buttons

### 6. **pages/recipe-detail.html** - Recipe Detail Page
- Large recipe image/display
- Detailed recipe information
- Recipe metadata (time, servings, cost, likes)
- Ingredients list with quantities
- Step-by-step cooking instructions
- Review section with:
  - Review submission form with rating
  - List of existing reviews
- Similar recipes sidebar

### 7. **pages/leaderboard.html** - Leaderboard Page
- Tab navigation (Top Creators, This Month, Highest Rated)
- Top 5 creators with:
  - Ranking badges
  - User info
  - Statistics
  - Total earnings display
- Platform statistics section

## Features Implemented

✅ **Responsive Design**
- Mobile-first approach
- Breakpoints for tablets and desktops
- Flexible grid layouts

✅ **Color Scheme**
- Primary: Red (#d32f2f)
- Secondary: Orange (#ffa726)
- Dark background: #1a1a1a
- Light background: #f5f5f5

✅ **Interactive Elements**
- Like/Unlike buttons
- Rating system (1-5 stars)
- Form validation ready
- Search functionality ready
- Tab navigation

✅ **Components**
- Recipe cards with hover effects
- Category cards
- User profile sections
- Review components
- Points display cards
- Leaderboard entries

## How to Use

### Opening the Website
1. Open `index.html` in any modern web browser
2. Navigate through pages using links

### Testing Different Pages
- **Home**: `index.html`
- **Create Recipe**: `frontend/pages/create-recipe.html`
- **Recipe Detail**: `frontend/pages/recipe-detail.html`
- **User Profile**: `frontend/pages/profile.html`
- **Leaderboard**: `frontend/pages/leaderboard.html`

## CSS Classes

### Buttons
- `.btn-primary` - Primary red button
- `.btn-secondary` - White button with border
- `.btn-like` - Like button
- `.btn-view` - View button

### Layout
- `.container` - Max-width container
- `.section` - Section with padding
- `.flex`, `.flex-between`, `.flex-center` - Flexbox utilities

### Messages
- `.success-message` - Green success notification
- `.error-message` - Red error notification

## JavaScript Functions

### Main Functions
- `setupEventListeners()` - Initialize all event listeners
- `setupTabNavigation()` - Tab switching functionality
- `setupRatingSystem()` - Rating stars interaction
- `handleFormSubmit()` - Form submission
- `updateLikeCount()` - Update like count

### Utility Functions
- `debounce()` - Debounce function calls
- `showNotification()` - Show toast notifications
- `formatDate()` - Format dates
- `getCurrentUser()` - Get current user from storage
- `saveUser()` - Save user to storage

### API Simulation Functions
- `createRecipe()` - Simulate recipe creation
- `submitReview()` - Simulate review submission
- `likeRecipe()` - Simulate like action

## Customization

### Colors
Edit CSS variables in `style.css`:
```css
:root {
  --primary-color: #d32f2f;
  --secondary-color: #ffa726;
  /* ... other colors ... */
}
```

### Fonts
Update font-family in `body` selector in `style.css`

### Content
Replace placeholder text, recipe names, and user information in HTML files

## Next Steps for Backend Integration

1. **API Endpoints Needed**:
   - `POST /api/recipes` - Create recipe
   - `GET /api/recipes` - Get recipes
   - `GET /api/recipes/:id` - Get recipe detail
   - `POST /api/reviews` - Submit review
   - `POST /api/likes` - Like a recipe
   - `GET /api/leaderboard` - Get leaderboard data
   - `GET /api/user/:id` - Get user profile

2. **Update JavaScript**:
   - Replace API simulation functions with real API calls
   - Implement proper error handling
   - Add loading states

3. **Database Integration**:
   - Connect to backend API
   - Implement user authentication
   - Store user sessions

## Browser Compatibility

- Chrome (Latest)
- Firefox (Latest)
- Safari (Latest)
- Edge (Latest)
- Mobile browsers

## Notes

- All images use emoji as placeholders. Replace with actual recipe images in production
- Form submissions are currently simulated. Connect to backend API for actual functionality
- User data is stored in localStorage. Implement proper session management for production
- Responsive design tested at common breakpoints (480px, 768px, 1200px)

## Future Enhancements

- [ ] Image optimization and lazy loading
- [ ] Progressive Web App (PWA) features
- [ ] Dark mode toggle
- [ ] Accessibility improvements (WCAG compliance)
- [ ] Animation enhancements
- [ ] Real-time notifications
- [ ] Advanced search and filtering
- [ ] Social sharing features
- [ ] Video recipe support

---

**Created**: October 26, 2025
**Platform**: Ranna-Kori - Bengali Recipe Sharing Platform
