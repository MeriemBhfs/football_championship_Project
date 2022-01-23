<?php

namespace App\Repositories;

class Ranking
{
    function goalDifference(int $goalFor, int $goalAgainst): int
    {
        return $goalFor - $goalAgainst;
    }

    function points(int $matchWonCount, int $drawMatchCount): int
    {
        return ($matchWonCount * 3) + $drawMatchCount;
    }

    function teamWinsMatch(int $teamId, array $match): bool
    {
        return (($teamId == $match['team0']) && ($match['score1'] < $match['score0'])) || (($teamId == $match['team1']) && ($match['score1'] > $match['score0']));
    }

    function teamLosesMatch(int $teamId, array $match): bool
    {
        return (($teamId == $match['team0']) && ($match['score1'] > $match['score0'])) || (($teamId == $match['team1']) && ($match['score1'] < $match['score0']));
    }

    function teamDrawsMatch(int $teamId, array $match): bool
    {
        return ($teamId == $match['team0'] || $teamId == $match['team1']) && $match['score1'] == $match['score0'];
    }

    function goalForCountDuringAMatch(int $teamId, array $match): int
    {
        return $teamId == $match['team0'] ? $match['score0'] : ($teamId == $match['team1'] ? $match['score1'] : 0);
    }

    function goalAgainstCountDuringAMatch(int $teamId, array $match): int
    {
        return $teamId == $match['team0'] ? $match['score1'] : ($teamId == $match['team1'] ? $match['score0'] : 0);
    }

    // Exo 14

    function goalForCount(int $teamId, array $matches): int
    {
        $sum = 0;
        foreach ($matches as $values) {
            $sum += $this->goalForCountDuringAMatch($teamId, $values);
        }
        return $sum;
    }

    function goalAgainstCount(int $teamId, array $matches): int
    {
        $sum = 0;
        foreach ($matches as $values) {
            $sum += $this->goalAgainstCountDuringAMatch($teamId, $values);
        }
        return $sum;
    }

    //Exo 15 

    function matchWonCount(int $teamId, array $matches): int
    {
        $sum = 0;
        foreach ($matches as $values) {
            if ($this->teamWinsMatch($teamId, $values)) {
                $sum++;
            }
        }
        return $sum;
    }

    function matchLostCount(int $teamId, array $matches): int
    {
        $sum = 0;
        foreach ($matches as $values) {
            if ($this->teamLosesMatch($teamId, $values)) {
                $sum++;
            }
        }
        return $sum;
    }

    function drawMatchCount(int $teamId, array $matches): int
    {
        $sum = 0;
        foreach ($matches as $values) {
            if ($this->teamDrawsMatch($teamId, $values)) {
                $sum++;
            }
        }
        return $sum;
    }

    // Exo 16

    function rankingRow(int $teamId, array $matches): array
    {
        $matchWonCount = $this->matchWonCount($teamId, $matches);
        $matchLostCount = $this->matchLostCount($teamId, $matches);
        $drawMatchCount = $this->drawMatchCount($teamId, $matches);
        $matchPlayedCount = $matchWonCount + $matchLostCount + $drawMatchCount;
        $goalForCount = $this->goalForCount($teamId, $matches);
        $goalAgainstCount = $this->goalAgainstCount($teamId, $matches);
        $goalDifference = $this->goalDifference($goalForCount, $goalAgainstCount);
        $points = $this->points($matchWonCount, $drawMatchCount);

        return [
            'team_id'            => $teamId,
            'match_played_count' => $matchPlayedCount,
            'match_won_count'    => $matchWonCount,
            'match_lost_count'   => $matchLostCount,
            'draw_count'         => $drawMatchCount,
            'goal_for_count'     => $goalForCount,
            'goal_against_count' => $goalAgainstCount,
            'goal_difference'    => $goalDifference,
            'points'             => $points
        ];
    }

    // Exo 17

    function unsortedRanking(array $teams, array $matches): array
    {
        $result = [];
        foreach ($teams as $values) {
            $result[] = $this->rankingRow($values['id'], $matches);
        }
        return $result;
    }

    // Exo 18

    static function compareRankingRow(array $row1, array $row2): int
    {
        if ($row1['points'] == $row2['points']) {
            if ($row1['goal_difference'] == $row2['goal_difference']) {
                if ($row1['goal_for_count'] == $row2['goal_for_count']) {
                    return 0;
                }
                return ($row1['goal_for_count'] > $row2['goal_for_count']) ? -1 : 1;
            }
            return ($row1['goal_difference'] > $row2['goal_difference']) ? -1 : 1;
        }
        return ($row1['points'] > $row2['points']) ? -1 : 1;
    }

    function sortedRanking(array $teams, array $matches): array
    {
        $result = $this->unsortedRanking($teams, $matches);
        usort($result, ['App\Repositories\Ranking', 'compareRankingRow']);
        for ($rank = 1; $rank <= count($result); $rank++) {
            // TODO : ajouter le rang dans le tableau associatif $result[$rank - 1] 
            $result[$rank - 1]['rank'] = $rank;
        }      
        return $result;
    }
}
