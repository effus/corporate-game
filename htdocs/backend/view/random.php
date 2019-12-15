<div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">

<h3>Random</h3>

<main role="main" class="inner cover mt-5">
    <p id="state0">Ждем начала раунда<br />Присоединилось игроков: <span class="badge badge-secondary gamersCount">0</span></p>
    <p id="state1" style="display: none;">Сейчас игрока определит случай</p>
    <h5 id="state2" style="display: none;">Жребий выпал игроку <span class="badge badge-warning gamer">%gamer%</span></h5>
    <p id="state3" style="display: none;">Игра завершена, всем спасибо!</p>
</main>

</div>

<script src="/frontend/js/axios.min.js"></script>
<script src="/frontend/js/random.js?r=<?=rand(0,100000)?>"></script>