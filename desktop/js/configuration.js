$('.bt_getinvite').off('click').on('click', function() {

    console.log(invitebotdiscord);
    var win = window.open(invitebotdiscord, '_blank');
    win.focus();
});

$('.bt_errorinvite').off('click').on('click', function() {

    console.log("https://discord.gg/Ac9Rwfh");
    var win = window.open("https://discord.gg/Ac9Rwfh", '_blank');
    win.focus();
});