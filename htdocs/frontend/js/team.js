const Team = {
    canContinue: true,
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
                Team.canContinue = true;
                setTimeout(Team.init, 1000);
            })
            .catch((err) => {
                console.error('getState', err);
                Team.canContinue = true;
                setTimeout(Team.init, 3000);
            });
    },
    update: (data) => {
        document.querySelector('.team-count').innerHTML = data.gamersCount;
    }
}

document.addEventListener('DOMContentLoaded', Team.init);
