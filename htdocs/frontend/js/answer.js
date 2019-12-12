const Answer = {
    canContinue: true,
    answerHash: '',
    isAnswered: false,
    init: function() {
        if (Answer.canContinue === true) {
            Answer.getState();
        } else {
            setTimeout(Answer.init, 1000);
        }
    },
    getState: function() {
        Answer.canContinue = false;
        axios.get('/?view=getAnswerState')
            .then((response) => {
                Answer.update(response.data);
                if (response.data.canAnswer) {
                    Answer.answerHash = response.data.hash;
                } else {
                    Answer.isAnswered = false;
                }
                Answer.canContinue = true;
                setTimeout(Answer.init, 1000);
            })
            .catch((err) => {
                console.error('getState', err);
                Answer.canContinue = true;
                setTimeout(Answer.init, 3000);
            });
    },
    update: (data) => {
        document.querySelector('.answer-btn-disabled').style = 'display: none;';
        document.querySelector('.answer-btn-enabled').style = 'display: none;';
        if (data.canAnswer && Answer.hash !== '') {
            document.querySelector('.answer-btn-enabled').style = 'display:;';
        } else {
            if (Answer.isAnswered) {
                document.querySelector('.answer-btn-disabled .comment').innerHTML = 'Говорите ответ!';
            } else {
                document.querySelector('.answer-btn-disabled .comment').innerHTML = 'Ждем следующего вопроса';
            }
            document.querySelector('.answer-btn-disabled').style = 'display:;';
        }
    },
    sendAnswer: function() {
        const hash = Answer.hash;
        Answer.hash = '';
        Answer.update();
        axios.get('/?view=sendAnswer&hash=' + hash)
            .then((response) => {
                Answer.isAnswered = response.data.isAnswered;
            })
            .catch((err) => {
                
            });
    }
}

document.addEventListener('DOMContentLoaded', Answer.init);