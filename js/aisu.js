function updateTotalLoginTurn() {

    post('api/aisu.php', {
        action: 'updateTotalLoginTurn',
        r: r
    }, function (response) {
        document.getElementById("total_login_turn").innerText = "　" + response.result + " 次";
    });
}