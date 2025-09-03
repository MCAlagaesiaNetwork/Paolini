window.onload = function() {
    var max = 17;
    var randomImage = Math.floor(Math.random() * max) + 1;
    document.body.style.background = "#5a5a5a url('https://mcalagaesia.com/img/background" + randomImage + ".jpg') center center / cover no-repeat fixed";
}