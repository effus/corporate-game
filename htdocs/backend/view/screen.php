<style>
.big-clock {
    font-size: 100px;
    background-color: #000;
}
</style>

<main class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">

<? include __DIR__ . '/header.php'; ?>

<main role="main" class="inner cover">
<div class="row">
    <div class="col">
        <h3 id="gameName" style="color:gray;">%game_name%</h3>
    </div>
</div>
<div class="row">
    <div class="col">
        <h1 id="roundName">%round_name%</h1>
    </div>
</div>
<div class="row" id="state">
    <div class="col">
        <h2><span class="badge badge-dark bg-gradient-secondary big-clock">%state%</span></h2>
    </div>
</div>
<div class="row mt-5" id="answering">
    <div class="col">
        <h3>
            <span class="badge badge-pill team">%team%</span>
        </h3>
    </div>
</div>


</main>

<? include __DIR__ . "/footer.php" ?>

</div>

<script src="/frontend/js/axios.min.js"></script>
<script src="/frontend/js/monitor.js?r=<?=rand(0,100000)?>"></script>