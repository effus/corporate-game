<div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
  
  <? include __DIR__ . '/header.php'; ?>

<main role="main" class="inner cover">
   

<div class="accordion" id="accordionExample">
    
  <div class="mt-3">
    <div class="" id="headingTwo">
      <?php if ($game): ?>
      <h2 class="mb-0">
        <button class="btn btn-secondary btn-lg collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          Присоединиться
        </button>
        <?php if($_SESSION['id']): ?>
        <script>let User = {registered: true};</script>
        <?php endif;?>
      </h2>
      <?php else: ?>
        <h2 class="mb-0">Ждем начала игры</h2>
        <script>
        setTimeout(() => document.location.reload(), 5000);
        </script>
      <?php endif; ?>
    </div>
    <div id="collapseTwo" class="collapse mt-2" aria-labelledby="headingTwo" data-parent="#accordionExample">
      <div class="card">
        <div class="card-body bg-dark">
          <form action="/?view=main&action=new" method="post" class="container-fluid">
            
            <div class="row">
              <div class="col">
                <input type="text" class="form-control w-100" name="gamer" placeholder="Новый игрок">
              </div>
              <div class="col-5">
                <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
              </div>
            </div>

          </form>

          <form action="/?view=main&action=connect" method="post" class="container-fluid">

            <?php if($_SESSION['id'] && $_SESSION['name']):?>
            <div class="row mt-3">
              <div class="col-2">
                Или: 
              </div>
              <div class="col">
                <input type="text" class="form-control w-100 bg-dark text-light" value="<?=$_SESSION['name']?>" disabled />
              </div>
              <div class="col-5">
                <button type="submit" class="btn btn-primary w-100">Присоединиться</button>
              </div>
            </div>
            <?php endif;?>

          </form>

          </div>
      </div>
    </div>
  </div>

</div>


  </main>

  <? include __DIR__ . "/footer.php" ?>

</div>
