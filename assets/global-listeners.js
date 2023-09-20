document.addEventListener("DOMContentLoaded", function() {

    // Add a click event listener to the body
    document.body.addEventListener("click", function(event) {

        // Check if the clicked element or its parent is a <code> block
        let target = event.target;
        while (target !== this && target.tagName.toLowerCase() !== 'code') {
            target = target.parentNode;
        }

        // If a <code> block was clicked, execute the copy logic
        if (target.tagName.toLowerCase() === 'code') {
            navigator.clipboard.writeText(target.textContent)
                .then(function() {
                    console.log("Copied: "+target.textContent);
                })
                .catch(function(err) {
                    console.error("Could not copy text: ", err);
                });
        }
    });
});

