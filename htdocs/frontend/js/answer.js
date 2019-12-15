const Answer = {
    //canContinue: true,
    answerHash: '',
    roundState: 0,
    currentAnswerId: null,
    canAnswer: false,
    myAnswerId: null,
    isBtnPressed: false,
    getStates: () => {
        return {
            PLAYED: 1,
            HAS_ANSWER: 2,
            FINISHED: 3,
            IN_PROGRESS: -1
        }
    },
    init: function() {
        if (!Answer.isBtnPressed) {
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
                    setTimeout(() => {
                        document.location.href = '/?view=team';
                    }, 3000);
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
            if (Answer.myAnswerId) {
                Answer.setAnswerDisabled();
                Answer.setComment('Ждем следующего раунда');
            } else {
                Answer.setAnswerEnabled();
                Answer.setComment('Можно отвечать');
            }
        } else if (Answer.roundState === Answer.getStates().IN_PROGRESS) {
            Answer.setAnswerDisabled()
            Answer.setComment('Отправка сигнала');
        } else if (Answer.roundState === Answer.getStates().HAS_ANSWER) {
            if (Answer.currentAnswerId === Answer.myAnswerId) {
                Answer.setComment('Ваш ответ!');
                Answer.setAnswerDisabled();
            } else {
                Answer.setComment('Кто-то ответил быстрее');
                Answer.setAnswerEnabled();
            }
        } else if (Answer.roundState === Answer.getStates().FINISHED) {
            Answer.setAnswerDisabled();
            Answer.setComment('Раунд завершен');
        } else {
            Answer.setComment('Ждем продолжения');
            Answer.setAnswerDisabled();
        }
    },
    setAnswerEnabled: () => {
        $('#btn-enabled').show();
        $('#btn-disabled').hide();
    },
    setAnswerDisabled: () => {
        $('#btn-enabled').hide();
        $('#btn-disabled').show();
    },
    setComment: (text) => {
        $('.comment').text(text);
    },
    onClickAnswer: () => {
        Answer.sendAnswer();
    }
};

document.addEventListener('DOMContentLoaded', Answer.init);
