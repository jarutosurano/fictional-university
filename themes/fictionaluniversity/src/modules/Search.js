import $ from 'jquery';

class Search {
    // 1. Describe and create/initiate our object
    constructor() {
        this.addSearchHTML(); // Dynamically add the search overlay HTML
        this.resultsDiv = $("#search-overlay__results"); // Container for search results
        this.openButton = $(".js-search-trigger"); // Button to open search overlay
        this.closeButton = $(".search-overlay__close"); // Button to close search overlay
        this.searchOverlay = $(".search-overlay"); // The search overlay element
        this.searchField = $("#search-term"); // Input field for search queries
        this.isOverlayOpen = false; // Track overlay state (open/closed)
        this.isSpinnerVisible = false; // Track spinner visibility during search
        this.previousValue = ""; // Store previous search value to avoid redundant searches
        this.typingTimer; // Timer to delay search request after typing
        this.events(); // Initialize event listeners
    }

    // 2. Events - Define user interactions (clicks, key presses)
    events() {
        // Open/close search overlay on button clicks
        this.openButton.on("click", this.openOverlay.bind(this));
        this.closeButton.on("click", this.closeOverlay.bind(this));

        // Key press events for shortcuts (e.g., open on 'S', close on 'Esc')
        $(document).on("keydown", this.keyPressDispatcher.bind(this));

        // Trigger search logic when typing in the input field
        this.searchField.on("keyup", this.typingLogic.bind(this));
    }

    // 3. Methods (functions/actions)

    // Handle input typing with a delay before making the search request
    typingLogic() {
        // Only run search if the value has changed
        if (this.searchField.val() !== this.previousValue) {
            clearTimeout(this.typingTimer); // Clear any previous timer

            if (this.searchField.val()) {
                // Show spinner if not already visible
                if (!this.isSpinnerVisible) {
                    this.resultsDiv.html('<div class="spinner-loader"></div>'); // Display spinner
                    this.isSpinnerVisible = true;
                }
                // Wait 500ms before sending the search request
                this.typingTimer = setTimeout(this.getResults.bind(this), 500);
            } else {
                // Clear results and hide spinner if input is empty
                this.resultsDiv.html("");
                this.isSpinnerVisible = false;
            }
        }
        // Update previous value to current input value
        this.previousValue = this.searchField.val();
    }

    // Fetch search results from both posts and pages, then display the combined results
    getResults() {
        $.getJSON(universityLocalize.root_url + '/wp-json/university/v1/search?term=' + this.searchField.val(), (results) => {
            this.resultsDiv.html(`
                <div class="row">
                    <div class="one-third">
                        <h2 class="search-overlay__section-title">General Information</h2>
                        ${results.generalInfo.length ? `<ul class="link-list min-list">` : `<p>No information matches that search.</p>`}
                            ${results.generalInfo.map(item => `<li><a href="${item.permalink}">${item.title}</a> ${item.postType === 'post' ? `by ${item.authorName}` : '' }</li>`).join("")}
                        ${results.generalInfo.length ? `</ul>` : ""}
                    </div>
                    
                    <div class="one-third">
                        <h2 class="search-overlay__section-title">Programs</h2>
                        ${results.programs.length ? `<ul class="link-list min-list">` : `<p>No programs match that search. <a href="${universityLocalize.root_url}/programs" >View all programs &raquo;</a></p>`}
                            ${results.programs.map(item => `<li><a href="${item.permalink}">${item.title}</a></li>`).join("")}
                        ${results.programs.length ? `</ul>` : ""}
                        
                        <h2 class="search-overlay__section-title">Professors</h2>
                        ${results.professors.length ? '<ul class="search-overlay__section-title professor-cards">' : `<p>No professors match that search.</p>`}
                            ${results.professors.map(item => `
                            <li class="professor-card__list-item">
                                <a class="professor-card" href="${item.permalink}">
                                    <img class="professor-card__image" src="${item.image}" alt="professor profile image">
                                    <span class="professor-card__name">${item.title}</span>
                                </a>
                            </li>
                            `).join("")}
                        ${results.professors.length ? '</ul>' : ""}
                    </div>
                    
                    <div class="one-third">
                        <h2 class="search-overlay__section-title">Campuses</h2>
                        ${results.campuses.length ? `<ul class="link-list min-list">` : `<p>No campuses match that search. <a href="${universityLocalize.root_url}/campuses" >View all campuses &raquo;</a></p>`}
                            ${results.campuses.map(item => `<li><a href="${item.permalink}">${item.title}</a></li>`).join("")}
                        ${results.campuses.length ? `</ul>` : ""}
                        
                        <h2 class="search-overlay__section-title">Events</h2>
                        ${results.events.length ? '' : `<p>No events match that search. <a href="${universityLocalize.root_url}/events" >View all events &raquo;</a></p>`}
                            ${results.events.map(item => `
                                <div class="event-summary">
                                    <a class="event-summary__date t-center" href="${item.permalink}">
                                        <span class="event-summary__month">${item.month}</span>
                                        <span class="event-summary__day">${item.day}</span>
                                    </a>
                                    <div class="event-summary__content">
                                        <h5 class="event-summary__title headline headline--tiny"><a href="${item.permalink}">${item.title}</a></h5>
                                        <p>${item.description}<a href="${item.permalink}" class="nu gray">Learn more &raquo;</a></p>
                                    </div>
                                </div>
                            `).join("")}
                    </div>
            </div>
            `);
            this.isSpinnerVisible = false;
        });
    }


    // Handle keyboard shortcuts for opening (S key) and closing (Esc key)
    keyPressDispatcher(e) {
        // Open overlay with 'S' key, unless input or textarea is focused
        if (e.keyCode === 83 && !this.isOverlayOpen && !$("input, textarea").is(':focus')) {
            this.openOverlay();
        }
        // Close overlay with 'Esc' key
        if (e.keyCode === 27 && this.isOverlayOpen) {
            this.closeOverlay();
        }
    }

    // Open the search overlay and focus the search field
    openOverlay() {
        this.searchOverlay.addClass("search-overlay--active"); // Show overlay
        $("body").addClass("body-no-scroll"); // Prevent body scrolling
        this.searchField.val(''); // Clear search field
        setTimeout(() => this.searchField.focus(), 301); // Focus search field with slight delay
        this.isOverlayOpen = true;
        return false;
    }

    // Close the search overlay and restore body scrolling
    closeOverlay() {
        this.searchOverlay.removeClass("search-overlay--active"); // Hide overlay
        $("body").removeClass("body-no-scroll"); // Enable body scrolling
        this.isOverlayOpen = false;
    }

    // Dynamically add the HTML structure for the search overlay
    addSearchHTML() {
        $("body").append(`
        <div class="search-overlay">
            <div class="search-overlay__top">
                <div class="container">
                    <i class="fa fa-search search-overlay__icon" aria-hidden="true"></i>
                    <input autocomplete="off" type="text" class="search-term" placeholder="What are you looking for?" id="search-term">
                    <i class="fa fa-window-close search-overlay__close" aria-hidden="true"></i>
                </div>
            </div>
            <div class="container">
                <div id="search-overlay__results"></div>
            </div>
        </div>
        `);
    }
}

export default Search;
