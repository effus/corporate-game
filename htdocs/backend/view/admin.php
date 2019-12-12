<main class="admin-container d-flex w-100 h-100 p-3 mx-auto flex-column">

<? include __DIR__ . '/header.php'; ?>

<main role="main" class="inner cover">
    

<div class="accordion" id="accordionExample">
  <div class="card">
    <div class="card-header" id="headingOne">
      <h2 class="mb-0">
        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
          Игры
        </button>
      </h2>
    </div>

    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
      <div class="card-body bg-dark">
        
      <!-- tab1 -->
      <div class="container">
  <div class="row">
    <div class="col-sm" style="overflow: auto; height: 200px;">
      
<ul class="list-group bg-dark">
  <li class="list-group-item active">Игра #2</li>
  <li class="list-group-item bg-dark">Игра #1</li>
</ul>

    </div>
    <div class="col-sm">
        <button class="btn btn-danger btn-lg">Новая игра</button> 
    </div>
  </div>
</div>
<!--/ tab1 -->

      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header" id="headingTwo">
      <h2 class="mb-0">
        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          Раунды
        </button>
      </h2>
    </div>
    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
      <div class="card-body bg-dark">
        
      
      <!-- tab1 -->
      <div class="container">
  <div class="row">
    <div class="col-4" style="overflow: auto; height: 200px;">
      
<ul class="list-group bg-dark">
  <li class="list-group-item active">Раунд #2</li>
  <li class="list-group-item bg-dark">Раунд #1</li>
</ul>

    </div>
    <div class="col-8">

    <ul class="list-group">
  <li class="list-group-item">
    <button class="btn btn-primary btn-sm">Новый раунд</button>
    <button class="btn btn-danger btn-sm">Старт</button>
  </li>
  <!-- li class="list-group-item">
    <div class="input-group">
      <input type="number" class="form-control" placeholder="время раунда" value="30">
      <div class="input-group-append">
        <span class="input-group-text" id="basic-addon1">сек</span>
      </div>
    </div>
  </!-->
  <li class="list-group-item">
    <span class="badge badge-secondary">ждем ответ</span>
  </li>
  <li class="list-group-item">
    
<div class="input-group mb-3">
  <div class="input-group-prepend" id="button-addon3">
    <button class="btn btn-outline-warning btn-sm" type="button">
      <img src="/frontend/icons/play.svg" width="24" height="24" title="Продолжить"> Продолжить
    </button>
  </div>
  <button class="btn btn-outline-success btn-sm" type="button">
    <img src="/frontend/icons/check.svg" width="24" height="24" title="Принять"> Да!
  </button>
  <div class="input-group-append">
    <button class="btn btn-outline-dark btn-sm" type="button">
    <img src="/frontend/icons/x-octagon.svg" width="24" height="24" title="Нет ответа"> Никто не знает
    </button>
  </div>
</div>

<button class="btn btn-dark btn-sm">Рандом</button>

  </li>
  
</ul>

    </div>
  </div>
</div>
<!--/ tab1 -->

      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header" id="headingThree">
      <h2 class="mb-0">
        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
          Команды
        </button>
      </h2>
    </div>
    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionExample">
      <div class="card-body bg-dark">
        
<ul class="list-group">
  <li class="list-group-item bg-dark d-flex justify-content-between align-items-center">
    Команда 1
    <span class="badge badge-primary badge-pill">14</span>
  </li>
  <li class="list-group-item bg-dark d-flex justify-content-between align-items-center">
    Команда 2
    <span class="badge badge-primary badge-pill">2</span>
  </li>
  <li class="list-group-item bg-dark d-flex justify-content-between align-items-center">
    Команда 3
    <span class="badge badge-primary badge-pill">1</span>
    <a href="#" class="btn btn-sm btn-warning">X</a>
  </li>
</ul>

      </div>
    </div>
  </div>
</div>


</main>

<? include __DIR__ . "/footer.php" ?>

</div>