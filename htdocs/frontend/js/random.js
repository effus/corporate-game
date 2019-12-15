const Random = {
    hash: '',
    roundState: 0,
    count: 0,
    choosen: null,
    getStates: () => {
        return {
            CREATED: 0,
            STARTED: 1,
            CHOOSED: 2,
            FINISHED: 3
        }
    },
    init: () => {
        Random.getState();
    },
    getState: () => {
        axios.get('/?view=getRoundRandomizerState&hash=' + Random.hash)
            .then((response) => {
                Random.roundState = response.data.roundState;
                Random.choosen = response.data.gamer;
                Random.count = response.data.gamersCount;
                Random.update();
                if (Random.roundState === Random.getStates().FINISHED) {
                    setTimeout(() => {
                        document.location.href = '/';
                    }, 3000);
                    return;
                }
                setTimeout(Random.init, 1000);
            })
            .catch((err) => {
                console.error('getState', err);
                setTimeout(Random.init, 3000);
            });
    },
    update: () => {
        $('#state0').hide();
        $('#state1').hide();
        $('#state2').hide();
        $('#state3').hide();
        if (Random.roundState === Random.getStates().CREATED) {
            $('#state0').show();
            $('#state0 .gamersCount').text(Random.count);
        } else if (Random.roundState === Random.getStates().STARTED) {
            $('#state1').show();
        } else if (Random.roundState === Random.getStates().CHOOSED) {
            $('#state2 .gamer').text(Random.choosen);
            $('#state2').show();
        } else if (Random.roundState === Random.getStates().FINISHED) {
            $('#state3').show();
        }

    }
};

document.addEventListener('DOMContentLoaded', Random.init);