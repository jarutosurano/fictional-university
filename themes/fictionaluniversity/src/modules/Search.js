import $ from 'jquery';

class Search {
    // 1. Describe and create/initiate our object
    constructor() {
        this.resultsDiv = $("#search-overlay__results"); // Container for search results
        this.openButton = $(".js-search-trigger"); // Element that triggers the search overlay
        this.closeButton = $(".search-overlay__close"); // Element that closes the search overlay
        this.searchOverlay = $(".search-overlay"); // The search overlay element
        this.searchField = $("#search-term"); // Input field for search queries
        this.isOverlayOpen = false; // Track the state of the overlay (open/close)
        this.isSpinnerVisible = false; // Track whether the loading spinner is visible
        this.previousValue = ""; // Store the previous value of the search input
        this.typingTimer; // Timer for managing typing delay
        this.events(); // Initialize event listeners
    }

    // 2. Events - Define all interactions with the search UI
    events() {
        // Event listeners for opening and closing the overlay
        this.openButton.on("click", this.openOverlay.bind(this));
        this.closeButton.on("click", this.closeOverlay.bind(this));

        // Handle key presses for opening/closing with keyboard shortcuts
        $(document).on("keydown", this.keyPressDispatcher.bind(this));

        // Detect typing in the search field
        this.searchField.on("keyup", this.typingLogic.bind(this));
    }

    // 3. Methods (functions, actions)

    // Handle logic when the user types in the search field
    typingLogic() {
        // Check if the current value differs from the previous value
        if (this.searchField.val() !== this.previousValue) {
            clearTimeout(this.typingTimer); // Reset the timer

            // If the input is not empty, show the spinner and start the timer
            if (this.searchField.val()) {
                if (!this.isSpinnerVisible) {
                    this.resultsDiv.html('<div class="spinner-loader"></div>'); // Display spinner
                    this.isSpinnerVisible = true;
                }
                // Trigger the search after a 2-second delay
                this.typingTimer = setTimeout(this.getResults.bind(this), 1500);
            } else {
                // Clear the results and hide the spinner if the input is empty
                this.resultsDiv.html("");
                this.isSpinnerVisible = false;
            }
        }

        // Store the current input value for comparison on the next keyup event
        this.previousValue = this.searchField.val();
    }

    // Simulate fetching and displaying search results
    getResults() {
        this.resultsDiv.html("Imagine real results here");
        this.isSpinnerVisible = false; // Hide the spinner after getting results
    }

    // Handle keyboard shortcuts (S key to open, Esc key to close)
    keyPressDispatcher(e) {
        // 'S' key (83) opens the overlay only if no input or textarea is focused
        if (e.keyCode === 83 && !this.isOverlayOpen && !$("input, textarea").is(':focus')) {
            this.openOverlay();
        }

        // 'Esc' key (27) closes the overlay if it's open
        if (e.keyCode === 27 && this.isOverlayOpen) {
            this.closeOverlay();
        }
    }

    // Open the search overlay and prevent body scrolling
    openOverlay() {
        this.searchOverlay.addClass("search-overlay--active");
        $("body").addClass("body-no-scroll"); // Prevent scrolling when overlay is active
        this.isOverlayOpen = true;
    }

    // Close the search overlay and allow body scrolling
    closeOverlay() {
        this.searchOverlay.removeClass("search-overlay--active");
        $("body").removeClass("body-no-scroll"); // Allow scrolling when overlay is closed
        this.isOverlayOpen = false;
    }
}

export default Search;
