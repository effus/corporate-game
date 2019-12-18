<style>
.big-clock {
    font-size: 50px;
    background-color: #000;
}
</style>

<main class="d-flex w-100 h-100 p-3 mx-auto flex-column">

<? include __DIR__ . '/header.php'; ?>

<main role="main" class="inner cover">
<div class="row mb-3">
    <div class="col">
        <h3 id="gameName" style="color:gray;">%game_name%</h3>
    </div>
</div>
<div class="row mb-3">
    <div class="col">
        <h5 id="info"></h5>
    </div>
</div>
<div class="row" id="state">
    <div class="col">
        <h2><span class="badge badge-dark bg-gradient-secondary big-clock">%state%</span></h2>
    </div>
</div>
<div class="help mt-3">
    <div class="col">
        <p>Добро пожаловать!</p>
        <p>Чтобы присоединиться, зайдите на своих мобильных девайсах по адресу: </p>
        <code class="mb-2" style="font-size:24px;">http://gemotest.effus.beget.tech</code>
        <p>Короткий URL: <code>http://bit.do/gemotest</code></p>
        <p><img src="/frontend/imgs/qr.png" height="250px;" /></p>
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