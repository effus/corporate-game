<div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
  
  <? include __DIR__ . '/header.php'; ?>

<main role="main" class="inner cover">
   

<div class="accordion" id="accordionExample">
    
  <div id="collapseOne" class="collapse mt-1" aria-labelledby="headingOne" data-parent="#accordionExample">
      <div class="card">
          <div class="card-body bg-dark">
            
<ul class="nav nav-pills">
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Все команды</a>
    <div class="dropdown-menu">
      <a class="dropdown-item" href="/?view=answer">Team 1</a>
    </div>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="#">
        <img src="/frontend/icons/arrow-clockwise.svg" width="24" height="24" title="Refresh">
    </a>
  </li>
</ul>


          </div>
      </div>
    
  </div>
  <div class="mt-3">
    <div class="" id="headingTwo">
      <h2 class="mb-0">
        <button class="btn btn-secondary btn-lg collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          Присоединиться
        </button>
      </h2>
    </div>
    <div id="collapseTwo" class="collapse mt-2" aria-labelledby="headingTwo" data-parent="#accordionExample">
      <div class="card">
        <div class="card-body bg-dark">
          <form action="/?view=main" method="post" class="container-fluid">
            
            <div class="row">
              <div class="col">
                <input type="text" class="form-control w-100" name="gamer" placeholder="Имя/Фамилия">
              </div>
              <div class="col-4">
                <button type="submit" class="btn btn-primary w-100">Ok</button>
              </div>
            </div>

          </form>

          </div>
      </div>
    </div>
  </div>

</div>


  </main>

  <? include __DIR__ . "/footer.php" ?>

</div>
