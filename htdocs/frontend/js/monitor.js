const Monitor = {

    canContinue: true,

    init: function() {
        if (Monitor.canContinue === true) {
            Monitor.getScore();
        } else {
            setTimeout(Monitor.init, 1000);
        }
    },

    getScore: function() {
        Monitor.canContinue = false;
        axios.get('/?view=getMonitorScores')
            .then((response) => {
                Monitor.update(response.data);
                Monitor.canContinue = true;
                setTimeout(Monitor.init, 1000);
            })
            .catch((err) => {
                console.error('getScore', err);
                Monitor.canContinue = true;
                setTimeout(Monitor.init, 3000);
            });
    },

    update: (data) => {
        document.querySelector('#gameName').innerHTML = data.game;
        document.querySelector('#info').innerHTML = data.info;
        document.querySelector('#state .big-clock').innerHTML = data.state;
        if (data.hasAnswer === true) {
            document.querySelector('#answering').style = 'display: block';
            document.querySelector('#answering .team').innerHTML = data.team;
            document.querySelector('#answering .team').classList.add('badge-secondary');
            if (data.result === true) {
                document.querySelector('#answering .team').classList.remove('badge-secondary');
                document.querySelector('#answering .team').classList.add('badge-success');
            } else if (data.result === false) {
                document.querySelector('#answering .team').classList.remove('badge-secondary');
                document.querySelector('#answering .team').classList.add('badge-danger');
            }
        } else {
            document.querySelector('#answering').style = 'display: none';
            document.querySelector('#answering .team').innerHTML = '';
        }
    }
}

document.addEventListener('DOMContentLoaded', Monitor.init);