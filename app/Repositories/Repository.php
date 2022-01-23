<?php

namespace App\Repositories;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Repositories\Data;
use App\Repositories\Ranking;

class Repository
{
    function createDatabase(): void
    {
        DB::unprepared(file_get_contents('database/build.sql'));
    }

    function insertTeam(array $team): int
    {
        return DB::table('teams')->insertGetId($team);
    }

    function insertMatch(array $match): int
    {
        return DB::table('matches')->insertGetId($match);
    }

    function teams(): array
    {
        return DB::table('teams')
            ->orderBy('id')
            ->get()
            ->toArray();
    }

    function matches(): array
    {
        return DB::table('matches')
            ->orderBy('id')
            ->get()
            ->toArray();
    }


    function fillDatabase(): void
    {
        $data = new Data();
        foreach ($data->teams() as $team) {
            $this->insertTeam($team);
        }

        foreach ($data->matches() as $match) {
            $this->insertMatch($match);
        }
    }

    function team($teamId): array
    {
        $table = DB::table('teams')->where('id', $teamId)
            ->get()
            ->toArray();
        if (count($table) == 0) {
            throw new Exception('Équipe inconnue');
        } else {
            return $table;
        }
    }

    function match($matchId): array
    {
        $table = DB::table('matches')->where('id', $matchId)
            ->get()
            ->toArray();
        if (count($table) == 0) {
            throw new Exception('Match inconnu');
        } else {
            return $table;
        }
    }

    function updateRanking(): void
    {
        DB::table('ranking')->delete();
        $teams = $this->teams();
        $matches = $this->matches();
        $ranking = new Ranking();
        $rankedTeams = $ranking->sortedRanking($teams, $matches);

        foreach ($rankedTeams as $team) {
            DB::table('ranking')->insert($team);
        }
    }

    function sortedRanking(): array
    {
        return DB::table('ranking as r')->join('teams as t', 'r.team_id', '=', 't.id')
            ->orderBy('r.rank')
            ->get(['r.*', 't.name'])
            ->toArray();
    }

    function teamMatches($teamId): array
    {
        return DB::table('matches as m')->join('teams as t0', 'm.team0', '=', 't0.id')
            ->join('teams as t1', 'm.team1', '=', 't1.id')
            ->where('m.team0', $teamId)
            ->orWhere('m.team1', $teamId)
            ->get(['m.*', 't0.name AS name0', 't1.name AS name1'])
            ->toArray();
    }

    
}
