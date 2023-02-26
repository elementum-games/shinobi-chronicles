<?php
/**
 * @var System $system
 * @var string $self_link
 * @var array|bool $user
 */
?>

<style>
    label {
        width: 9em;
        margin-left: 1rem;
        display: inline-block;
    }
    .stat_display {
        width: 75%;
        margin: 1rem auto;
        padding: 5px;
        border: 1px solid black;
    }
    p {
        display: inline-block;
        margin: 0;
    }
</style>

<script>
    function update() {
        let cut_amount = parseInt(document.getElementById('cut_amount').value);
        let cut_percent = parseFloat(1 - (cut_amount / 100), 2).toFixed(2);
        let cut_ai = document.getElementById('cut_ai').checked;
        let cut_pvp = document.getElementById('cut_pvp').checked;
        let cut_yen = document.getElementById('cut_yen').checked;

        document.getElementById('pvp_display').style.display = (cut_pvp ? 'block' : 'none');
        document.getElementById('ai_display').style.display = (cut_ai ? 'block' : 'none');
        document.getElementById('yen_display').style.display = (cut_yen ? 'block' : 'none');

        let exp = <?=$user['exp']?>;
        let ai_wins = <?=$user['ai_wins']?>;
        let pvp_wins = <?=$user['pvp_wins']?>;
        let yen = <?=$user['money']?>;

        let nin_skill = <?=$user['ninjutsu_skill']?>;
        let tai_skill = <?=$user['taijutsu_skill']?>;
        let gen_skill = <?=$user['genjutsu_skill']?>;
        let bl_skill = <?=$user['bloodline_skill']?>;
        let cast_sp = <?=$user['cast_speed']?>;
        let speed = <?=$user['speed']?>;
        let int = <?=$user['intelligence']?>;
        let will = <?=$user['willpower']?>;

        let new_exp = Math.floor(exp * cut_percent).toLocaleString();
        let new_ai_wins = Math.floor(ai_wins * cut_percent).toLocaleString();
        let new_pvp_wins = Math.floor(pvp_wins * cut_percent).toLocaleString();
        let new_money = Math.floor(yen * cut_percent).toLocaleString();

        let new_nin = Math.floor(nin_skill * cut_percent).toLocaleString();
        let new_tai = Math.floor(tai_skill * cut_percent).toLocaleString();
        let new_gen = Math.floor(gen_skill * cut_percent).toLocaleString();
        let new_bl = Math.floor(bl_skill * cut_percent).toLocaleString();
        let new_cast_sp = Math.floor(cast_sp * cut_percent).toLocaleString();
        let new_speed = Math.floor(speed * cut_percent).toLocaleString();
        let new_int = Math.floor(int * cut_percent).toLocaleString();
        let new_will = Math.floor(will * cut_percent).toLocaleString();

        document.getElementById('exp').innerHTML = new_exp;
        document.getElementById('ai_wins').innerHTML = new_ai_wins;
        document.getElementById('pvp_wins').innerHTML = new_pvp_wins;
        document.getElementById('yen').innerHTML = new_money;

        document.getElementById('nin_skill').innerHTML = new_nin;
        document.getElementById('tai_skill').innerHTML = new_tai;
        document.getElementById('gen_skill').innerHTML = new_gen;
        document.getElementById('bl_skill').innerHTML = new_bl;
        document.getElementById('cast_sp').innerHTML = new_cast_sp;
        document.getElementById('speed').innerHTML = new_speed;
        document.getElementById('int').innerHTML = new_int;
        document.getElementById('will').innerHTML = new_will;
    }
</script>

<table class="table">
    <tr><th>Stat Cut <?=($user ? " - {$user['user_name']}({$user['user_id']})" : " - Select User")?></th></tr>
    <tr>
        <?php if(!$user): ?>
            <td style="text-align: center;">
                <form action="<?=$self_link?>" method="post">
                    Username: <input type="text" name="user_name" />
                    <input type="submit" name="set_user" />
                </form>
        <?php else: ?>
            <td>
                <form action="<?=$self_link?>" method="post">
                    <input type="hidden" name="user_id" value="<?=$user['user_id']?>" />
                    <label>Cut Amount(%):</label><input type="number" onchange="update()" name="cut_amount"
                            id="cut_amount" value="50" step="5" min="5" max="95"/><br />
                    <label>Cut AI Wins:</label><input type="checkbox" onchange="update()" name="cut_ai" id="cut_ai" checked="checked"/><br />
                    <label>Cut PvP Wins:</label><input type="checkbox" onchange="update()" name="cut_pvp" id="cut_pvp" checked="checked"/><br />
                    <label>Cut Yen:</label><input type="checkbox" onchange="update()" name="cut_yen" id="cut_yen" checked="checked"/><br />
                    <div class="stat_display">
                        <h3>Yen & Battle Data</h3>
                        <label>Exp:</label><?=number_format($user['exp'])?> => <p id="exp"><?=number_format(floor($user['exp']/2))?></p><br />
                        <div id="ai_display">
                            <label>AI Wins:</label><?=number_format($user['ai_wins'])?> => <p id="ai_wins"><?=number_format(floor($user['ai_wins']/2))?></p>
                        </div>
                        <div id="pvp_display">
                            <label>PvP Wins:</label><?=number_format($user['pvp_wins'])?> => <p id="pvp_wins"><?=number_format(floor($user['pvp_wins']/2))?></p>
                        </div>
                        <div id="yen_display">
                            <label>Yen:</label><?=number_format($user['money'])?> => <p id="yen"><?=number_format(floor($user['money']/2))?></p>
                        </div>
                        <h3>Stats</h3>
                        <label>Ninjutsu Skill:</label><?=number_format($user['ninjutsu_skill'])?> => <p id="nin_skill"><?=number_format(floor($user['ninjutsu_skill']/2))?></p><br />
                        <label>Taijutsu Skill:</label><?=number_format($user['taijutsu_skill'])?> => <p id="tai_skill"><?=number_format(floor($user['taijutsu_skill']/2))?></p><br />
                        <label>Genjutsu Skill:</label><?=number_format($user['genjutsu_skill'])?> => <p id="gen_skill"><?=number_format(floor($user['genjutsu_skill']/2))?></p><br />
                        <label>Bloodline Skill:</label><?=number_format($user['bloodline_skill'])?> => <p id="bl_skill"><?=number_format(floor($user['bloodline_skill']/2))?></p><br />
                        <label>Cast Speed:</label><?=number_format($user['cast_speed'])?> => <p id="cast_sp"><?=number_format(floor($user['cast_speed']/2))?></p><br />
                        <label>Speed:</label><?=number_format($user['speed'])?> => <p id="speed"><?=number_format(floor($user['speed']/2))?></p><br />
                        <label>Intelligence:</label><?=number_format($user['intelligence'])?> => <p id="int"><?=number_format(floor($user['intelligence']/2))?></p><br />
                        <label>Willpower:</label><?=number_format($user['willpower'])?> => <p id="will"><?=number_format(floor($user['willpower']/2))?></p>
                    </div>
                    <div style="text-align: center;"><input type="submit" name="cut_stats" value="Cut Stats" /></div>
                </form>
        <?php endif ?>
            </td>
    </tr>
</table>
