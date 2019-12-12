const Admin = {
    currentRound: {
        id: null,
        state: 0,
        gameType: 1,
        hasAnswer: null,
    },
    gamers: [],
    teamsCount: 0,
    canContinue: true,
    init: function() {
        Admin.updateRoundPanelState();
        if (Admin.currentRound.state === 1) {
            Admin.canContinue = true;
            Admin.checkAnswer();
        }
        if (!Admin.currentRound.id) {
            Admin.getGamers();
        }
    },
    getGamers: function() {
        if (Admin.currentRound.id) {
            return;
        }
        axios.get('/?view=getGamersList')
            .then((response) => {
                Admin.gamers = response.data.gamers;
                Admin.teamsCount = response.data.teamsCount;
                Admin.updateGamersList();
                setTimeout(Admin.getGamers, 5000);
            })
            .catch((err) => {
                setTimeout(Admin.getGamers, 5000);
            });
    },
    checkAnswer: function() {
        if (!Admin.canContinue) {
            return;
        }
        axios.get('/?view=getAdminCheckAnswer&round_id=' + Admin.currentRound.id)
            .then((response) => {
                setTimeout(Admin.checkAnswer, 1000);
            })
            .catch((err) => {
                Admin.setRoundStateLabel('Ошибка: ' + err.message);
                setTimeout(Admin.checkAnswer, 3000);
            });
    },
    updateGamersList: () => {
        $('#gamersCount').text(Admin.gamers.length);
        $('#teamsCount').text(Admin.teamsCount);
    },
    updateRoundsList: (rounds) => {
        $('.rounds-list .list-group-item').remove();
        let html = '';
        for(let i in rounds) {
            html += '<li class="list-group-item ' + (rounds[i].id === Admin.currentRound.id ? 'active' : '') + '">Раунд #' +rounds[i].id+ '</li>';
        }
        $('.rounds-list').append(html);
    },
    updateRoundPanelState: () => {
        $('.round-control-buttons button').hide();
        if (!Admin.currentRound.id) {
            Admin.setRoundStateLabel('Создайте новый раунд');
            return;
        }
        if (Admin.currentRound.gameType === 1) {
            if (Admin.currentRound.state === 0) {
                Admin.setRoundStateLabel('Начните раунд');
                $('#startRound').show();
            } else if (Admin.currentRound.state === 1) {
                Admin.setRoundStateLabel('Ждем ответа');
                if (Admin.currentRound.hasAnswer) {
                    $('#continueRound').show();
                    $('#commitAnswer').show();
                } else {
                    $('#noOne').show();
                }
            }
        } else {
            Admin.setRoundStateLabel('Запустите рандомайзер');
            $('#startRandom').show();
        }
    },
    setRoundStateLabel: (label) => {
        $('#currentState').text(label);
    },
    onClickNewGame: () => {
        axios.get('/?view=newGame&type=' + $('#gameType').val())
            .then((response) => {
                document.location.reload();
            })
            .catch((err) => {
                console.error('onClickNewGame', err);
                Admin.setRoundStateLabel('Ошибка: ' + err.message);
            });
    },
    onClickCreateCommands: () => {
        const commandCount = $('#commandCount').val();
        axios.get('/?view=createCommands&count=' + commandCount)
            .then((response) => {
                //document.location.reload();
            })
            .catch((err) => {
                console.error('onClickNewGame', err);
                Admin.setRoundStateLabel('Ошибка: ' + err.message);
            });
    },
    onClickNewRound: () => {
        axios.get('/?view=newRound')
            .then((response) => {
                Admin.currentRound.id = response.data.result;
                Admin.currentRound.state = 0;
                Admin.updateRoundsList(response.data.rounds);
                Admin.updateRoundPanelState();
            })
            .catch((err) => {
                console.error('onClickNewRound', err);
                Admin.setRoundStateLabel('Ошибка: ' + err.message);
            });
    },
    onClickStartRound: () => {
        axios.get('/?view=startRound')
            .then((response) => {
                Admin.currentRound.state = 1;
                Admin.canContinue = true;
                Admin.updateRoundPanelState();
                Admin.checkAnswer();
            })
            .catch((err) => {
                console.error('onClickStartRound', err);
                Admin.setRoundStateLabel('Ошибка: ' + err.message);
            });
    },
    onClickCommitAnswer: () => {

    },
    onClickContinueAnswer: () => {

    },
    onClickNoAnswer: () => {

    },
    onClickRandom: () => {

    }
};

document.addEventListener('DOMContentLoaded', Admin.init);
