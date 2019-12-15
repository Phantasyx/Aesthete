<?php

namespace Steampunk;


class GamesView extends View
{
    private $site;
    public function __construct(Site $site)
    {
       // $this->setTitle("Games has no title");
        $this->site = $site;
    }

    public function present(){
        $html = <<<HTML
        
<body>

<div class="welcome_main"><img src="images/title.png" alt="Title"><br>
    <div class="ChooseGameType">
        <form action="post/games.php" class="games" method="post">
        <table class="games">
        <tbody>
		<tr>
			<th>&nbsp;</th>
			<th>Room Name</th>
			<th>Creator</th>
			<th>Size</th>
		</tr>
HTML;
        $games = new Games($this->site);
        $rows = $games->get();
        if (!is_null($rows)){
            foreach ($rows as $game){
                $id = $game['id'];
                $game_name = $game['gamename'];
                $creator = $game['p1_name'];
                $size = $game['size'];
                $html.=<<<HTML
<tr>
    <td><input type="radio" name ="id" value="$id"></td>
    <td><input type="hidden" name="game_name" value=$game_name>$game_name</td>
    <td>$creator</td>
    <td>$size</td>
</tr>
HTML;
            }
        }else{
            $html.=<<<HTML
<p>Currently there are no game, please <a href="NewGame.php">make a game</a></p>
HTML;
        }

        $html.=<<<HTML
        </tbody>
		</table>
		<input type="submit" name="join" value="Join">
		<input type="submit" name="back" value="Back">
        </form>
    </div>
</div>
</body>

HTML;
        return $html;
    }
}