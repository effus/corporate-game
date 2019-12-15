const Admin = {
    currentRound: {
        id: null,
        state: 0,
        gameType: 1,
        answer: {},
        checking: false
    },
    gamers: [],
    teamsCount: 0,
    teams: [],
    canContinue: true,
    init: function() {
        Admin.updateRoundPanelState();
        if (Admin.currentRound.state === 1 || Admin.currentRound.state === 2) {
            Admin.canContinue = true;
            Admin.checkAnswer();
        }
        if (!Admin.currentRound.id) {
            Admin.getGamers();
        }
        Admin.onClickRefreshTeamList();
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
            })
            .catch((err) => {
                console.debug('err', err);
            });
    },
    checkAnswer: function() {
        if (!Admin.canContinue) {
            return;
        }
        Admin.currentRound.checking = true;
        axios.get('/?view=getAdminCheckAnswer&round_id=' + Admin.currentRound.id)
            .then((response) => {
                Admin.currentRound.state = response.data.roundState;
                Admin.currentRound.answer = response.data.answer;
                Admin.updateRoundPanelState();
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
                Admin.setRoundStateLabel('Начните раунд, после чего можете задавать вопрос');
                $('#startRound').show();
            } else if (Admin.currentRound.state === 1) {
                Admin.setRoundStateLabel('Ждем ответа');
                $('#noOne').show();
            } else if (Admin.currentRound.state === 2) {
                if (Admin.currentRound.answer) {
                    Admin.setRoundStateLabel('Ответил: ' + Admin.currentRound.answer.gamer_name + (Admin.currentRound.answer.team_name ? ' / ' + Admin.currentRound.answer.team_name : ''));
                } else {
                    Admin.setRoundStateLabel('Кто-то ответил');
                }
                $('#continueRound').show();
                $('#commitAnswer').show();
            } else if (Admin.currentRound.state === 3) {
                if (Admin.currentRound.answer && Admin.currentRound.answer.gamer_name) {
                    Admin.setRoundStateLabel('Правильный ответ: ' + Admin.currentRound.answer.gamer_name + (Admin.currentRound.answer.team_name ? ' / ' + Admin.currentRound.answer.team_name : ''));
                } else {
                    Admin.setRoundStateLabel('Раунд завершен без ответа');
                }
                $('#continueRound').hide();
                $('#commitAnswer').hide();
                $('#noOne').hide();
            }
        } else {
            if (Admin.currentRound.state === 0) {
                Admin.setRoundStateLabel('Начните раунд, на мониторе запустится виазулизация');
                $('#startRound').show();
            }  else if (Admin.currentRound.state === 1) {
                Admin.setRoundStateLabel('Определить рандомного игрока');
                $('#startRandom').show();
            }  else if (Admin.currentRound.state === 2) {
                if (Admin.currentRound.answer) {
                    Admin.setRoundStateLabel('Выбран игрок: ' + Admin.currentRound.answer.gamer_name + (Admin.currentRound.answer.team_name ? ' / ' + Admin.currentRound.answer.team_name : ''));
                } else {
                    Admin.setRoundStateLabel('Кто-то выбран...');
                }
                $('#commitAnswer').show();
                $('#startRandom').hide();
            }  else if (Admin.currentRound.state === 3) {
                if (Admin.currentRound.answer) {
                    Admin.setRoundStateLabel('Выбран и подтвержден игрок: ' + Admin.currentRound.answer.gamer_name + (Admin.currentRound.answer.team_name ? ' / ' + Admin.currentRound.answer.team_name : ''));
                } else {
                    Admin.setRoundStateLabel('Кто-то выбран и подтвержден...');
                }
            }
        }
    },
    updateTeamsList: () => {
        let html = '';
        for(let i in Admin.teams) {
            html += '<a href="javascript:" class="list-group-item list-group-item-action" data-team-id="' + i + '">' + Admin.teams[i].name 
            + ' <span class="badge badge-primary badge-pill">' + Admin.teams[i].scores + '</span></a>';
        }
        $('#teamlist').append(html);
        $('#teamlist a').click(Admin.onClickGetTeamMembers);
    },
    updateTeamMembers: (teamId) => {
        $('#teamgamers li').remove();
        let html = '';
        for(let i in Admin.teams[teamId].members) {
            html += '<li class="list-group-item bg-dark d-flex justify-content-between align-items-center">' + 
                Admin.teams[teamId].members[i].name + ' <span class="badge badge-primary badge-pill">' + 
                Admin.teams[teamId].members[i].scores + '</span></li>';
        }
        $('#teamgamers').append(html);
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
    onClickApplyAnswer: () => {
        console.debug('apply!');
        axios.get('/?view=adminApplyCurrentAnswer')
            .then((response) => {
                Admin.updateRoundPanelState();
            })
            .catch((err) => {
                console.error('onClickCommitAnswer', err);
                Admin.setRoundStateLabel('Ошибка: ' + err.message);
            });
    },
    onClickDenyAnswer: () => {
        axios.get('/?view=adminDenyCurrentAnswer')
            .then((response) => {
                Admin.currentRound.answer = {};
            })
            .catch((err) => {
                console.error('onClickContinueAnswer', err);
                Admin.setRoundStateLabel('Ошибка: ' + err.message);
            });
    },
    onClickNoAnswer: () => {
        axios.get('/?view=adminNoAnswerInRound')
            .then((response) => {
                Admin.currentRound.answer = {};
            })
            .catch((err) => {
                console.error('onClickContinueAnswer', err);
                Admin.setRoundStateLabel('Ошибка: ' + err.message);
            });
    },
    onClickEndGame: () => {
        Admin.canContinue = false;
        axios.get('/?view=adminEndCurrentGame')
            .then((response) => {
                Admin.currentRound.id = null;
                Admin.currentRound.state = 0;
                
            })
            .catch((err) => {
                console.error('onClickEndGame', err);
                Admin.setRoundStateLabel('Ошибка: ' + err.message);
            });
    },
    onClickRandom: () => {
        console.debug('onClickRandom');
        axios.get('/?view=adminRunRandom')
            .then((response) => {
                
            })
            .catch((err) => {
                console.error('onClickRandom', err);
                Admin.setRoundStateLabel('Ошибка: ' + err.message);
            });
    },
    onClickRefreshTeamList: () => {
        $('#teamlist a').remove();
        axios.get('/?view=getTeamList')
            .then((response) => {
                Admin.teams = response.data.result;
                Admin.updateTeamsList();
            })
            .catch((err) => {
                console.debug('err', err);
            });
    },
    onClickGetTeamMembers: (event) => {
        Admin.updateTeamMembers($(event.target).data('team-id'));
    }
};

document.addEventListener('DOMContentLoaded', Admin.init);
