$(function () {
    var origTitle, animatedTitle, timer;
    var funmessage = [
        "Hey, come back!",
        "Don't go!",
        "I miss you!",
        "Feeling lonely!",
        "Still here!",
        "Waiting for you!",
        "Come back soon!",
        "Don't leave me!",
        "I'm bored!",
        "Miss you already!",
        "Still waiting!",
        "Come back, please!",
        "I need you!",
        "Waiting patiently!",
        "Your screen misses you!"
    ];
    
    function animateTitle(newTitle) {
        var currentState = false;
        origTitle = document.title;  // save original title
        animatedTitle = newTitle || funmessage[Math.floor(Math.random() * funmessage.length)]; // get new message
        timer = setInterval(startAnimation, 5000);

        function startAnimation() {
            // animate between the original and the new title
            document.title = currentState ? origTitle : animatedTitle;
            currentState = !currentState;
        }
    }

    function restoreTitle() {
        clearInterval(timer);
        document.title = origTitle; // restore original title
    }

    // Change page title on blur
    $(window).blur(function () {
        animateTitle();
        $('#blurifAway').addClass('away');
        $('.b_navbar').addClass('d-none');
    });

    // Change page title back on focus
    $(window).focus(function () {
            restoreTitle();
            $('#blurifAway').removeClass('away');
            $('.b_navbar').removeClass('d-none');
    });

});