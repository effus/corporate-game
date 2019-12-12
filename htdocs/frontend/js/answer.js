const Answer = {
    //canContinue: true,
    answerHash: '',
    roundState: 0,
    currentAnswerId: null,
    canAnswer: false,
    myAnswerId: null,
    getStates: () => {
        return {
            PLAYED: 1,
            HAS_ANSWER: 2,
            FINISHED: 3,
            IN_PROGRESS: -1
        }
    },
    init: function() {
        if (Answer.canContinue === true) {
            Answer.getState();
        } else {
            setTimeout(Answer.init, 1000);
        }
    },
    getState: function() {
        axios.get('/?view=getRoundState&hash=' + Answer.answerHash)
            .then((response) => {
                Answer.roundState = response.data.roundState;
                Answer.currentAnswerId = response.data.currentAnswer;
                Answer.update();
                if (Answer.roundState === Answer.getStates().FINISHED) {
                    document.location.href = '/?view=team';
                    return;
                }
                setTimeout(Answer.init, 1000);
            })
            .catch((err) => {
                console.error('getState', err);
                setTimeout(Answer.init, 3000);
            });
    },
    sendAnswer: function() {
        Answer.roundState = Answer.getStates().IN_PROGRESS;
        Answer.currentAnswerId = -1;
        Answer.update();
        axios.get('/?view=sendAnswer&hash=' + Answer.answerHash)
            .then((response) => {
                Answer.myAnswerId = response.data.answerId;
                Answer.currentAnswerId = response.data.currentAnswerId;
                Answer.roundState = response.data.roundState;
                Answer.update();
            })
            .catch((err) => {
                console.error('sendAnswer', err);
            });
    },
    update: () => {
        if (Answer.roundState === Answer.getStates().PLAYED) {
            Answer.canAnswer = true;
        } else if (Answer.roundState === Answer.getStates().HAS_ANSWER) {
            Answer.canAnswer = true; // ответы принимаются после первого ответа
        } else {
            Answer.canAnswer = false;
        }
        if (Answer.canAnswer === true) {
            document.querySelector('.answer-btn-disabled').style = 'display: none;';
            document.querySelector('.answer-btn-enabled').style = 'display:;';
        } else {
            document.querySelector('.answer-btn-enabled').style = 'display: none;';
            document.querySelector('.answer-btn-disabled').style = 'display:;';
            if (Answer.currentAnswerId === Answer.myAnswerId) {
                document.querySelector('.answer-btn-disabled .comment').innerHTML = 'Слушаем ваш ответ!';
            } else if (Answer.roundState === Answer.getStates().IN_PROGRESS) {
                document.querySelector('.answer-btn-disabled .comment').innerHTML = 'Смотрим, кто первый';
            } else if (Answer.myAnswerId) {
                document.querySelector('.answer-btn-disabled .comment').innerHTML = 'Вас опередили';
            } else {
                document.querySelector('.answer-btn-disabled .comment').innerHTML = 'Ответы пока не принимаются';
            }
        }
    },

};

document.addEventListener('DOMContentLoaded', Answer.init);
