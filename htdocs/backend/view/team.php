<?php
/**
 * @var $rounds array
 */
?>

<div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
  
<? include __DIR__ . '/header.php'; ?>

  <main role="main" class="inner cover" id="noTeamnoGame" style="display: none;">
  <?php if ( count($rounds) === 0 ): ?>
    <h3>Мы формируем команды</h3>
    <p>Сейчас регистрируются участники. Когда все будут готовы, мы случайным образом 
       поделим вас поровну (если это возможно) и начнем игру.</p>
    <h2 class="mb-3 gamers-count-container" style="display: none;">
        <span class="badge badge-warning gamer-count">0</span> <span id="memberlabel"></span>
    </h2>
    <p>Кстати, названия командам тоже будут даны случайным образом. Но вы их сможете 
        сменить. Наверное. Если успеете.</p>
  
  <?php endif; ?>

  <p class="mt-5">Ждем начала следующего раунда</p>

</main>

  <main role="main" class="inner cover" id="IhaveATeam" style="display: none;">

  <?php if ( count($rounds) > 0 ): ?>
    <h3>Раунд завершен</h3>

      <?php if($lastWinner): ?>
        <p>Правильный ответ дал <span class="badge badge-danger"> <?=$lastWinner['name']?> </span></p>
      <?php else: ?>
        <p>Правильный ответ никто не дал</p>
      <? endif; ?>

      <p class="mt-2 mb-5">Ждем начала следующего раунда</p>

    <?php endif; ?>

    <h5>Вы в команде 
      <a href="javascript:Team.onClickChangeName()">
        <span class="badge badge-primary team-name">%teamname%</span> 
      </a>
    </h5>
    <div class="row mb-5">
      <div class="col">
        Менять имя разрешено один раз за игру одному из игроков команды. 
      </div>
    </div>

    <div>Состав вашей команды: </div>
    <ul class="list-group" id="members">
    </ul>

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
  </main>

  <? include __DIR__ . "/footer.php" ?>

</div>

<script src="/frontend/js/axios.min.js"></script>
<script src="/frontend/js/team.js?r=<?=rand(0,100000)?>"></script>