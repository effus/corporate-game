<?php
/**
 * @var $games array
 * @var $currentGame
 * @var $rounds array
 * @var $currentRound array
 */
?>

<div class="admin-container d-flex w-100 h-100 p-3 mx-auto flex-column">
    
    <? include __DIR__ . '/header.php'; ?>

    <main role="main" class="inner cover">


        <div class="accordion" id="accordionExample">
            <div class="card">
                <div class="card-header" id="headingOne">
                    <h2 class="mb-0">
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne"
                                aria-expanded="true" aria-controls="collapseOne">
                            Игры
                        </button>
                    </h2>
                </div>

                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne"
                     data-parent="#accordionExample">
                    <div class="card-body bg-dark">

                        <!-- tab1 -->
                        <div class="container">
                            <div class="row">
                                <div class="col-2">
                                    <select id="gameType" class="form-control">
                                        <option value="1">Кто быстрее ответит</option>
                                        <option value="2">Рандом</option>
                                    </select>
                                    <button class="btn btn-danger btn-lg" onclick="Admin.onClickNewGame()">Новая игра</button>
                                </div>
                                <div class="col" style="overflow: auto; height: 200px;">
                                    <ul class="list-group bg-dark">
                                        <? foreach ($games as $game): ?>
                                            <li class="list-group-item <?= (!$game['finished_at']) ? 'active' : '' ?>">
                                                Игра #<?= $game['id'] ?></li>
                                        <? endforeach; ?>
                                    </ul>
                                </div>
                                <div class="col">
                                    <button class="btn btn-warning btn-sm mt-3" onclick="Admin.onClickEndGame()">Завершить</button>
                                </div>
                                <div class="col">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" placeholder="Количество комманд" value="2" id="commandCount">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" onclick="Admin.onClickCreateCommands()">Создать команды</button>
                                        </div>
                                    </div>
                                    <div><span class="badge badge-secondary" id="gamersCount">0</span> игроков</div>
                                    <div><span class="badge badge-secondary" id="teamsCount">0</span> команд</div>
                                    <div><button type="button" onclick="Admin.getGamers()">Get counts</button></div>
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
                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse"
                                data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
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

                                    <ul class="list-group bg-dark rounds-list">
                                        <? foreach ($rounds as $round): ?>
                                            <li class="list-group-item <?= (!$round['finished_at']) ? 'active' : '' ?>">
                                                Раунд #<?= $round['id'] ?></li>
                                        <? endforeach; ?>
                                    </ul> 

                                </div>
                                <div class="col-8">

                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <button class="btn btn-primary btn-sm" onclick="Admin.onClickNewRound()">
                                                Новый раунд
                                            </button>
                                            <button class="btn btn-secondary btn-sm" onclick="Admin.checkAnswer()">Проверить</button>
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
                                            <span class="badge badge-secondary" id="currentState">ждем ответ</span>
                                        </li>
                                        <li class="round-control-buttons list-group-item d-flex">
                                            <button id="startRound" class="btn btn-danger btn-sm flex-fill" onclick="Admin.onClickStartRound()">Старт!</button>
                                            <button id="commitAnswer" class="btn btn-success btn-sm flex-fill" type="button" onclick="Admin.onClickApplyAnswer()">
                                                <img src="/frontend/icons/check.svg" width="24" height="24"
                                                     title="Принять"> Правильно!
                                            </button>
                                            <button id="continueRound" class="btn btn-warning btn-sm flex-fill" type="button" onclick="Admin.onClickDenyAnswer()">
                                                <img src="/frontend/icons/play.svg" width="24" height="24"
                                                     title="Продолжить"> Продолжить раунд
                                            </button>
                                            <button id="noOne" class="btn btn-dark btn-sm flex-fill" type="button" onclick="Admin.onClickNoAnswer()">
                                                <img src="/frontend/icons/x-octagon.svg" width="24" height="24"
                                                     title="Нет ответа"> Никто не знает
                                            </button>
                                            <button id="startRandom" class="btn btn-dark btn-sm flex-fill" onclick="Admin.onClickRandom()">Рандом</button>
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
                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse"
                                data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Команды
                        </button>
                    </h2>
                </div>
                <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionExample">
                    <div class="card-body bg-dark">
                        <div class="row">
                            <div class="col col-2">
                                <button type="button" class="btn btn-dark btn-sm" onclick="Admin.onClickRefreshTeamList()">Refresh</button>
                            </div>
                            <div class="col">
                                <div class="list-group" id="teamlist"></div>
                            </div>
                            <div class="col">
                                <ul class="list-group" id="teamgamers"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </main>
    
    <? include __DIR__ . "/footer.php" ?>

</div>

<script src="/frontend/js/axios.min.js"></script>
<script src="/frontend/js/admin.js?r=<?= rand(0, 100000) ?>"></script>
<script>
    Admin.currentRound.id = <?=$currentRound ? $currentRound['id'] : 'null'?>;
    Admin.currentRound.state = <?=$currentRound ? intval($currentRound['state']) : '0'?>;
    Admin.currentRound.gameType = <?=$currentGame ? intval($currentGame['type']) : '1'?>;
</script>
