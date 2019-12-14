<?php
/**
 * @var $answerHash string
 */
?>

<style>
button {
    font-size: 50px;
}
</style>

<div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">

<? include __DIR__ . '/header.php'; ?>

<main role="main" class="inner cover">
<div class="row">
    <div id="comment" class="col">
        <p class="comment">%comment%</p>
    </div>
</div>
<div class="row">
    <div class="col" id="btn-disabled" style="display: none;">
        <button class="btn btn-secondary btn-lg answer-btn" disabled>Ответ</button>
    </div>
    <div class="col" id="btn-enabled" style="display: none;">
        <button class="btn btn-secondary btn-lg answer-btn" onclick="Answer.onClickAnswer()">Ответ</button>
    </div>
</div>

</main>

<? include __DIR__ . "/footer.php" ?>

</div>

<script src="/frontend/js/axios.min.js"></script>
<script src="/frontend/js/answer.js?r=<?=rand(0,100000)?>"></script>

<script>
    Answer.answerHash = '<?=$answerHash?>';
    Answer.currentAnswerId = <?=$answer ? intval($answer['id']) : 'null'?>;
    Answer.myAnswerId = <?=$myAnswer ? intval($myAnswer['id']) : 'null'?>;
</script>
