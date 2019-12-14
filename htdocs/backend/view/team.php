<div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
  
<? include __DIR__ . '/header.php'; ?>

  <main role="main" class="inner cover" id="noTeamnoGame" style="display: none;">
   <h3>Мы формируем команды</h3>

   <p>Сейчас регистрируются участники. Когда все будут готовы, мы случайным образом 
       поделимся поровну (если это возможно) и начнем игру.</p> 

    <h2 class="mb-3 gamers-count-container" style="display: none;">
        <span class="badge badge-warning gamer-count">0</span> участников
    </h2>

    <p>Кстати, название командам тоже будут даны случайным образом. Но вы их сможете 
        сменить. Наверное. Если успеете.</p>
  </main>

  <main role="main" class="inner cover" id="IhaveATeam" style="display: none;">
    <h3>Вы в команде <a href="javascript:Team.onClickChangeName()"><span class="badge badge-success team-name">%teamname%</span></a> </h3>

    <ul class="list-group" id="members">
      <li class="list-group-item bg-dark">Cras justo odio</li>
    </ul>

    <p class="mt-5">Ждем начала раунда</p>

    <div class="row mt-5" id="teamNameForm" style="display:none;">
      <div class="col">
        <div class="input-group mb-3">
          <input type="text" class="form-control" id="teamName" value="">
          <div class="input-group-append">
            <button class="btn btn-success" type="button" onclick="Team.onClickSetName()">Изменить</button>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        Менять имя разрешено один раз за игру одному из игроков команды. 
      </div>
    </div>
  </main>

  <? include __DIR__ . "/footer.php" ?>

</div>

<script src="/frontend/js/axios.min.js"></script>
<script src="/frontend/js/team.js?r=<?=rand(0,100000)?>"></script>