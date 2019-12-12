CREATE TABLE `answers` (
  `id` int(11) NOT NULL,
  `gamer_id` int(11) NOT NULL,
  `round_id` int(11) NOT NULL,
  `dt` datetime NOT NULL,
  `flag_correct` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `answers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

---

CREATE TABLE `gamers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `game_id` int(11) NOT NULL,
  `team_id` int(11) DEFAULT NULL,
  `scores` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `gamers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `gamers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

---

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '1',
  `finished_at` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

---

CREATE TABLE `rounds` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `state` int(11) NOT NULL DEFAULT '1',
  `current_answer_id` int(11) DEFAULT NULL,
  `winner_team` int(11) DEFAULT NULL,
  `winner_user` int(11) DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `rounds`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `rounds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

---

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `scores` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
