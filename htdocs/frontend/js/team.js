const Team = {
    canContinue: true,
    name: null,
    members: [],
    gamersCount: 0,
    allowChangeName: false,
    init: function() {
        if (Team.canContinue === true) {
            Team.getState();
        } else {
            setTimeout(Team.init, 1000);
        }
    },
    getState: function() {
        Team.canContinue = false;
        axios.get('/?view=getTeamState')
            .then((response) => {
                Team.update(response.data);
                if (response.data.ready === true) {
                    document.location.href = '/?view=answer';
                    return;
                }
                Team.gamersCount = response.data.gamersCount;
                if (Team.gamersCount === 0) {
                    document.location.href = '/';
                    return;
                }
                Team.canContinue = true;
                Team.name = response.data.team;
                Team.members = response.data.members;
                Team.allowChangeName = response.data.allowChangeName;
                setTimeout(Team.init, 1000);
            })
            .catch((err) => {
                console.error('getState', err);
                Team.canContinue = true;
                setTimeout(Team.init, 3000);
            });
    },
    update: (data) => {
        if (Team.name) {
            $('#noTeamnoGame').hide();
            $('.team-name').text(Team.name);
            $('#IhaveATeam').show();
            $('#members li').remove();
            let html = '';
            for(let i in Team.members) {
                html += '<li class="list-group-item bg-dark">' +Team.members[i]+ '</li>';
            }
            $('#members').append(html);
        } else {
            $('.gamers-count-container').show();
            $('.gamer-count').text(Team.gamersCount);
            $('#noTeamnoGame').show();
            $('#IhaveATeam').hide();
        }
    },
    onClickChangeName: () => {
        if (Team.allowChangeName) {
            $('#teamNameForm').show();
        }
    },
    onClickSetName: () => {
        axios.get('/?view=setTeamName&name=' + encodeURI($('#teamName').val()))
            .then((response) => {
                $('#teamNameForm').hide();
            })
            .catch((err) => {
                console.error('onClickSetName', err);
                $('#teamNameForm').hide();
            });
    }
}

document.addEventListener('DOMContentLoaded', Team.init);
