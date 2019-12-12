<div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
  
<? include __DIR__ . '/header.php'; ?>

  <main role="main" class="inner cover">
   <h3>Мы формируем команды</h3>

   <p>Сейчас регистрируются участники. Когда все будут готовы, мы случайным образом 
       поделимся поровну (если это возможно) и начнем игру.</p> 

    <h2 class="mb-3">
        <span class="badge badge-warning team-count">13</span> участников
    </h2>

    <p>Кстати, название командам тоже будут даны случайным образом. Но вы их сможете 
        сменить. Наверное. Если успеете.</p>
  </main>

  <? include __DIR__ . "/footer.php" ?>

</div>

<script src="/frontend/js/axios.min.js"></script>
<script src="/frontend/js/team.js?r=<?=rand(0,100000)?>"></script>