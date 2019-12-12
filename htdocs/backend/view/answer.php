<style>
button {
    font-size: 50px;
}
</style>

<div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">

<? include __DIR__ . '/header.php'; ?>

<main role="main" class="inner cover">
<div class="row">
    <div class="col answer-btn-enabled" style="display: none;">
        <p>Можно отвечать!</p>
        <p>
            <button class="btn btn-success btn-lg answer-btn" onclick="Answer.sendAnswer()">Ответ</button>
        </p>
    </div>
    <div class="col answer-btn-disabled">
        <p class="comment">Ждем следующего вопроса</p>
        <p>
            <button class="btn btn-secondary btn-lg answer-btn" disabled>Ответ</button>
        </p>
    </div>
</div>


</main>

<? include __DIR__ . "/footer.php" ?>

</div>

<script src="/frontend/js/axios.min.js"></script>
<script src="/frontend/js/answer.js?r=<?=rand(0,100000)?>"></script>