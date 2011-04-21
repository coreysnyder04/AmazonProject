<?php

class Default_Model_AmazonService
{
	protected $db;
	protected $games;
	protected $leagues;
	protected $locations;
	protected $sessions;
	protected $teams;
	
	function __construct()
	{
		$config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini', APPLICATION_ENV);
		
		$options = array(
			'host'      => $config->db->host,
			'username'	=>	$config->db->username,
			'password'	=>	$config->db->password,
			'dbname'	=>	$config->db->dbname
		);
		
		$this->db = Zend_Db::factory($config->db->adapter, $options);
		Zend_Db_Table_Abstract::setDefaultAdapter($this->db);
		
		/* 
		$this->games = new GamesTable();
		$this->leagues = new LeaguesTable();
		$this->locations = new LocationsTable();
		$this->sessions = new SessionsTable();
		$this->teams = new TeamsTable();
		*/
	}
	
	/* Leagues */
	
	public function getAllData()
	{
		
		$sql = "SELECT * FROM users";
	
		return $this->db->fetchAll($sql);
		
	}
	
	public function getLeagueNameById($leagueId)
	{
		
		$sql = "SELECT name FROM leagues WHERE ID = '".$leagueId."'";
		
		return $this->db->fetchAll($sql);
		
	}
	
	public function getLeagueNameByAlias($alias)
	{
		
		$sql = "SELECT name FROM leagues WHERE alias = '".$alias."'";
		
		return $this->db->fetchAll($sql);
		
	}

	
	public function getLeagueInfoByID($leagueID){
		
		$sql = "SELECT * FROM leagues WHERE ID = '".$leagueID."'";
		
		return $this->db->fetchAll($sql);
			
	}
	
	public function getLeagueIDByAlias($leagueAlias){
		
		$sql = "select ID from leagues where alias = '".$leagueAlias."'";
		
		return $this->db->fetchAll($sql);
		
	}

	public function getSessionIdByAlias($sessionAlias){
		
		$sql = "select * from sessions where alias = '".$sessionAlias."'";
		
		return $this->db->fetchAll($sql);
		
	}
	
	public function getCurrentSessionByLeague($leagueAlias){
		
		$sql = "select sessionid,s.Name
				from teamhistory th
				LEFT OUTER JOIN leagues l on l.id=th.leagueID
				LEFT OUTER JOIN sessions s on s.id=th.sessionID 
				where l.alias='".$leagueAlias."'
				order by sessionid desc
				limit 1";
		
		return $this->db->fetchAll($sql);
		
	}
	
	public function getCurrentInfoByTeamAlias($teamAlias){
		$sql = "select sessionid,leagueid,s.alias
				from teamhistory th
				left outer join teams t on t.ID=th.teamid
				left outer join sessions s on s.ID=th.sessionID
				where t.alias='".$teamAlias."'
				order by sessionid desc
				limit 1";
				
		return $this->db->fetchAll($sql);
		
	}

	/* Scores */
	
	public function getRecentScoresByLeague($league){
		
		$sql = "SELECT
				g.ID               AS GameID,
				DATE_FORMAT(g.Date, '%b %e, %Y') AS GameDate,
				teamHome.ID        AS HomeID,
				teamHome.teamname      AS HomeTeam,
				teamsHome.alias      AS HomeAlias,
				g.ScoreHome        AS HomeScore,
				teamAway.id        AS AwayID,
				teamAway.teamname      AS AwayTeam,
				teamsAway.alias     AS AwayAlias,
				g.ScoreAway        AS AwayScore,
				g.OTL			AS OTL
				FROM
				games AS g
				LEFT OUTER JOIN leagues AS l ON l.id = g.LeagueID
				LEFT OUTER JOIN teamhistory AS teamAway ON teamAway.id = g.teamAwayID
				LEFT OUTER JOIN teamhistory AS teamHome ON teamHome.id = g.teamHomeID
				LEFT OUTER JOIN teams as teamsHome ON teamsHome.ID=teamHome.teamID
				LEFT OUTER JOIN teams as teamsAway ON teamsAway.ID=teamAway.teamID
				LEFT OUTER JOIN (
				SELECT
				leagueID,
				DAY(MAX(DATE)) MaxDay,
				MONTH(MAX(DATE)) MaxMonth,
				YEAR(MAX(DATE)) MaxYear
				FROM games
				WHERE games.ScoreHome IS NOT NULL
				GROUP BY leagueID
				)
				AS MaxDate ON MaxDate.leagueid = g.LeagueID
				WHERE
				l.alias = '".$league."'
					AND DAY(g.Date) = MaxDate.MaxDay
					AND MONTH(g.date) = MaxDate.MaxMonth
					AND YEAR(g.date) = MaxDate.MaxYear
					 ORDER BY GameID, DATE ASC";
		return $this->db->fetchAll($sql);
	}
	
	/* Schedule */
	
	public function getSchedule($league,$sessionID) {
		
		$sql = "SELECT
				DATE_FORMAT(g.date, '%b %e, %Y') AS Date,
				DATE_FORMAT(g.time, '%l:%i PM') AS Time,
				l.name            AS Location,
				g.locationSection AS Section,
				th1.teamname           AS HomeTeam,
				t1.alias      	AS HomeAlias,
				th2.teamname           AS AwayTeam,
				t2.alias      	AS AwayAlias
				FROM
				games g
				LEFT OUTER JOIN locations l on l.ID = g.locationID
				LEFT OUTER JOIN leagues d ON g.leagueID = d.ID
				LEFT OUTER JOIN teamhistory th1 ON th1.ID = g.teamHomeID
				LEFT OUTER JOIN teamhistory th2 ON th2.ID = g.teamAwayID
				LEFT OUTER JOIN teams t1 ON t1.ID = th1.teamID
				LEFT OUTER JOIN teams t2 ON t2.ID = th2.teamID
				WHERE
				g.date >= CURDATE()
				AND g.date < DATE_ADD(CURDATE(), INTERVAL 7 DAY)
				AND g.sessionID = '".$sessionID."'
				AND d.alias = '".$league."'
				ORDER BY
				g.time ASC";
		
		return $this->db->fetchAll($sql);
				
	}
	public function getTeamHistory($teamID, $sessionID){
		//Returns: sessionAlias, leagueName, teamName, GP, GF, GA
		$sql = "select s.alias SessionAlias, s.name as teamName, UPPER(s.alias) as SessionAliasCaps,l.name LeagueName,l.alias LeagueAlias,th.teamID,th.teamName,t.alias,
				COUNT(CASE WHEN g.scoreHome IS NOT NULL THEN 1 ELSE NULL END) AS GP,
				SUM(CASE WHEN g.teamHomeID=th.ID THEN scoreHome ELSE scoreAway END) GF,
				SUM(CASE WHEN g.teamAwayID<>th.ID THEN scoreAway ELSE scoreHome END) GA,
				COUNT(CASE WHEN g.TeamHomeID=th.ID THEN CASE WHEN g.scoreHome > g.scoreAway THEN 1 ELSE NULL END ELSE CASE WHEN  g.scoreHome < g.scoreAway THEN 1 ELSE NULL END END) AS W,
				COUNT(CASE WHEN g.OTL=0 THEN CASE WHEN g.teamHomeID=th.id THEN CASE WHEN g.scoreHome < g.scoreAway THEN 1 ELSE NULL END ELSE CASE WHEN  g.scoreHome > g.scoreAway THEN 1 ELSE NULL END END END) AS L,
				COUNT(CASE WHEN g.scoreHome=g.scoreAway THEN 1 ELSE NULL END) AS T,
				COUNT(CASE WHEN g.OTL=1 THEN CASE WHEN g.teamHomeID=th.id THEN CASE WHEN g.scoreAway > g.scoreHome THEN 1 ELSE NULL END END END ) + 
				COUNT(CASE WHEN g.OTL=1 THEN CASE WHEN g.teamAwayID=th.id THEN CASE WHEN g.scoreHome > g.scoreAway THEN 1 ELSE NULL END END END ) AS OTL,
				(COUNT(CASE WHEN g.teamHomeID=th.id THEN CASE WHEN g.scoreHome > g.scoreAway THEN 1 ELSE NULL END ELSE CASE WHEN  g.scoreHome < g.scoreAway THEN 1 ELSE NULL END END) * 2)+(COUNT(CASE WHEN g.scoreHome=g.scoreAway THEN 1 ELSE NULL END))+(COUNT(CASE WHEN g.OTL=1 THEN CASE WHEN g.teamHomeID=th.id THEN CASE WHEN g.scoreAway > g.scoreHome THEN 1 ELSE NULL END END END ) + 
				COUNT(CASE WHEN g.OTL=1 THEN CASE WHEN g.teamAwayID=th.id THEN CASE WHEN g.scoreHome > g.scoreAway THEN 1 ELSE NULL END END END )) AS Pts
				FROM
				teamhistory AS th
				LEFT OUTER JOIN games AS g ON th.ID = g.teamHomeID OR th.ID = g.teamAwayID
				left outer join leagues l on l.ID=g.leagueid
				left outer join sessions s on s.ID=g.sessionid
				left outer join teams t on t.ID=th.teamID
				where s.id <> '".$sessionID."'
				and th.teamID='".$teamID."'
				group by s.alias,l.name,th.teamName
				order by s.ID desc";
							
		return $this->db->fetchAll($sql);
		
	}
	public function getScheduleByTeamID($teamID, $sessionID) {			
		$sql = "SELECT DATE_FORMAT(date, '%b %e, %Y')    AS GameDate,
				DATE_FORMAT(time,'%l:%i PM')            AS GameTime,
				l.name        AS location,
				s.alias 		AS sessionAlias,
				g.GameTypeID 		AS gameType,
				g.ID as gameID,
				g.locationSection    AS locationSection,
				g.hasDetails as hasDetails,
				scoreHome,scoreAway,
				thHome.teamName                AS HomeTeam,
				home.alias HomeAlias,
				thAway.teamName                AS AwayTeam,
				away.alias AwayAlias,
				CASE
				WHEN g.OTL=0 THEN
				CASE
				WHEN thHome.ID=".$teamID." THEN
				CASE
				WHEN scoreHome>scoreAway THEN Concat('W ' , scoreHome , '-' , scoreAway)
				WHEN scoreAway > scoreHome THEN Concat('L ' , scoreHome , '-' , scoreAway)
				WHEN scoreHome=scoreAway THEN Concat('T ' , scoreHome , '-' , scoreAway)
				ELSE ''
				END
				WHEN thAway.ID=".$teamID." THEN
				CASE
				WHEN scoreHome<scoreAway THEN Concat('W ' , scoreAway , '-' , scoreHome)
				WHEN scoreAway<scoreHome THEN Concat('L ' , scoreAway , '-' , scoreHome)
				WHEN scoreHome=scoreAway THEN Concat('T ' , scoreAway , '-' , scoreHome)
				ELSE ''
				END
				END
				WHEN g.OTL=1 THEN
				CASE
				WHEN thHome.ID=".$teamID." THEN
				CASE
				WHEN scoreHome>scoreAway THEN Concat('W ' , scoreHome , '-' , scoreAway)
				WHEN scoreAway > scoreHome THEN Concat('OTL ' , scoreHome , '-' , scoreAway)
				ELSE ''
				END
				WHEN thAway.ID=".$teamID." THEN
				CASE
				WHEN scoreHome<scoreAway THEN Concat('W ' , scoreAway , '-' , scoreHome)
				WHEN scoreAway<scoreHome THEN Concat('OTL ' , scoreAway , '-' , scoreHome)
				ELSE ''
				END
				END
				END Outcome
				FROM games g
				LEFT OUTER JOIN locations l ON g.locationID=l.ID
				LEFT OUTER JOIN sessions s ON s.ID=g.sessionID
				LEFT OUTER JOIN teamhistory thHome on thHome.ID=g.teamHomeID
				LEFT OUTER JOIN teamhistory thAway on thAway.ID=g.teamAwayID
				LEFT OUTER JOIN teams home ON home.ID=thHome.teamID
				LEFT OUTER JOIN teams away ON away.id=thAway.teamID
				WHERE (thHome.ID=".$teamID." OR thAway.ID=".$teamID.")
				AND g.sessionID=".$sessionID."
				ORDER BY date";
			
		return $this->db->fetchAll($sql);
		
	}
	
	/* Standings */
	
	public function getStandingsByLeague($league,$sessionID){
		
		$sql = 
			"SELECT
				th.teamname AS TeamName,
				t.alias AS TeamAlias,
				s.alias as sessionAlias,
				l.subscribeLink as subscribeLink,
				COUNT(CASE WHEN g.scoreHome IS NOT NULL THEN 1 ELSE NULL END) AS GP,
				SUM(CASE WHEN g.teamHomeID=th.ID THEN scoreHome ELSE scoreAway END) GF,
				SUM(CASE WHEN g.teamAwayID<>th.ID THEN scoreAway ELSE scoreHome END) GA,
				COUNT(CASE WHEN g.teamHomeID=th.id THEN CASE WHEN g.scoreHome > g.scoreAway THEN 1 ELSE NULL END ELSE CASE WHEN  g.scoreHome < g.scoreAway THEN 1 ELSE NULL END END) AS W,
				COUNT(CASE WHEN g.OTL=0 THEN CASE WHEN g.teamHomeID=th.id THEN CASE WHEN g.scoreHome < g.scoreAway THEN 1 ELSE NULL END ELSE CASE WHEN  g.scoreHome > g.scoreAway THEN 1 ELSE NULL END END END) AS L,
				COUNT(CASE WHEN g.scoreHome=g.scoreAway THEN 1 ELSE NULL END) AS T,
				COUNT(CASE WHEN g.OTL=1 THEN CASE WHEN g.teamHomeID=th.id THEN CASE WHEN g.scoreAway > g.scoreHome THEN 1 ELSE NULL END END END ) + 
				COUNT(CASE WHEN g.OTL=1 THEN CASE WHEN g.teamAwayID=th.id THEN CASE WHEN g.scoreHome > g.scoreAway THEN 1 ELSE NULL END END END ) AS OTL,
				(COUNT(CASE WHEN g.teamHomeID=th.id THEN CASE WHEN g.scoreHome > g.scoreAway THEN 1 ELSE NULL END ELSE CASE WHEN  g.scoreHome < g.scoreAway THEN 1 ELSE NULL END END) * 2)+(COUNT(CASE WHEN g.scoreHome=g.scoreAway THEN 1 ELSE NULL END))+(COUNT(CASE WHEN g.OTL=1 THEN CASE WHEN g.teamHomeID=th.id THEN CASE WHEN g.scoreAway > g.scoreHome THEN 1 ELSE NULL END END END ) + 
				COUNT(CASE WHEN g.OTL=1 THEN CASE WHEN g.teamAwayID=th.id THEN CASE WHEN g.scoreHome > g.scoreAway THEN 1 ELSE NULL END END END )) AS Pts
				FROM
				teamhistory AS th
				LEFT OUTER JOIN games AS g ON th.ID = g.teamHomeID OR th.ID = g.teamAwayID
				LEFT OUTER JOIN leagues AS l on l.ID = th.leagueID
				LEFT OUTER JOIN teams t on t.ID=th.teamID
				LEFT OUTER JOIN sessions s on s.ID=th.sessionID
				WHERE
				l.alias = '".$league."'
				AND g.sessionID = '".$sessionID."'
				AND g.GameTypeID=1
                GROUP BY
                th.teamName,
                th.leagueID,
                th.teamid,
                g.SessionID
                ORDER BY
                Pts DESC,
                GA,
                GF DESC,
                GP DESC";		
		
		return $this->db->fetchAll($sql);
		
	}
	
	/* Team */
	
	public function getTeamInfobyAlias($teamAlias, $sessionAlias){
		$sql = "SELECT t.alias alias, th.ID ID, th.teamID teamID, th.teamName teamName, t.name name, th.teamName,th.sessionID,th.leagueID, s.name as sessionName, s.alias as sessionAlias
				FROM teams t LEFT OUTER JOIN teamhistory th on th.teamID = t.ID
				LEFT OUTER JOIN sessions s on s.ID=th.sessionID
				WHERE t.alias ='".$teamAlias."'
				and s.alias='".$sessionAlias."'
				ORDER BY th.ID DESC";
		return $this->db->fetchAll($sql);
			
	}
	
	public function getAllTeams(){
		$sql = 
			"SELECT
				s.alias SessionAlias,
				s.name SessionName,
				th.ID        AS TeamID,
				th.teamname      AS TeamName,
				t.alias AS TeamAlias, l.ID AS LeagueID, l.name AS LeagueName, l.day AS LeagueDay, l.level AS LeagueLevel, l.division AS LeagueDivision
			FROM teamhistory AS th
				LEFT OUTER JOIN leagues AS l on th.leagueID = l.id
				LEFT OUTER JOIN teams t on t.ID=th.teamID
				LEFT OUTER JOIN sessions s on s.ID=th.sessionid
				ORDER BY TeamName, LeagueID, TeamAlias ";
		
		return $this->db->fetchAll($sql);
		
	}
	
	//Playoffs
	public function getPlayoffGameCount($leagueID, $sessionID){
		//Retrieves a count of round 1 playoff games for a particular league and session. 
		//This is helpful for determining if we should show the playoff tables
		$sql = "SELECT COUNT(*) as playoffs
				FROM games 
				LEFT OUTER JOIN leagues AS l on l.ID = games.leagueID
				where sessionID='".$sessionID."' 
				AND GameTypeID='2'
				AND l.alias='".$leagueID."'";
		
		return $this->db->fetchAll($sql);
	}
	//Round 1 games
	public function getPlayoffScores($leagueAlias, $sessionID, $round){
		
		$sql = "SELECT
				g.ID               AS GameID,
				DATE_FORMAT(g.Date, '%b %e, %Y') AS GameDate,
				DATE_FORMAT(time,'%l:%i PM') AS GameTime,
				HomeRank.rank HomeRank,
				teamHome.ID        AS HomeID,
				teamHome.teamname      AS HomeTeam,
				teamsHome.alias      AS HomeAlias,
                                s.alias AS sessionAlias,
				g.ScoreHome        AS HomeScore,
				AwayRank.rank AwayRank,
				teamAway.id        AS AwayID,
				teamAway.teamname      AS AwayTeam,
				teamsAway.alias     AS AwayAlias,
				g.ScoreAway        AS AwayScore,
				g.OTL			AS OTL
				FROM
				games AS g
				LEFT OUTER JOIN leagues AS l ON l.id = g.LeagueID
				LEFT OUTER JOIN teamhistory AS teamAway ON teamAway.id = g.teamAwayID
				LEFT OUTER JOIN teamhistory AS teamHome ON teamHome.id = g.teamHomeID
				LEFT OUTER JOIN teams as teamsHome ON teamsHome.ID=teamHome.teamID
LEFT OUTER JOIN sessions s on s.ID=g.sessionID
				LEFT OUTER JOIN teams as teamsAway ON teamsAway.ID=teamAway.teamID
				LEFT OUTER JOIN (
				SELECT @homerownum:=@homerownum + 1 as rank,
				TeamName,
				teamID
				FROM 
				(
				SELECT
				th.teamname AS TeamName,
				th.ID teamID
				FROM
				teamhistory AS th
				LEFT OUTER JOIN games AS g ON th.ID = g.teamHomeID OR th.ID = g.teamAwayID
				LEFT OUTER JOIN leagues AS l on l.ID = th.leagueID
				LEFT OUTER JOIN teams t on t.ID=th.teamID
				LEFT OUTER JOIN sessions s on s.ID=th.sessionID
				WHERE
				l.alias = '".$leagueAlias."'
				AND g.sessionID = '".$sessionID."'
				AND g.GameTypeID=1
				GROUP BY
				th.teamName,
				th.leagueID,
				th.teamid,
				g.SessionID
				ORDER BY
				(COUNT(CASE WHEN g.teamHomeID=th.id THEN CASE WHEN g.scoreHome > g.scoreAway THEN 1 ELSE NULL END ELSE CASE WHEN  g.scoreHome < g.scoreAway THEN 1 ELSE NULL END END) * 2)+(COUNT(CASE WHEN g.scoreHome=g.scoreAway THEN 1 ELSE NULL END))+(COUNT(CASE WHEN g.OTL=1 THEN CASE WHEN g.teamHomeID=th.id THEN CASE WHEN g.scoreAway > g.scoreHome THEN 1 ELSE NULL END END END ) + 
				COUNT(CASE WHEN g.OTL=1 THEN CASE WHEN g.teamAwayID=th.id THEN CASE WHEN g.scoreHome > g.scoreAway THEN 1 ELSE NULL END END END )) DESC,
				SUM(CASE WHEN g.teamAwayID<>th.ID THEN scoreAway ELSE scoreHome END),
				SUM(CASE WHEN g.teamHomeID=th.ID THEN scoreHome ELSE scoreAway END) DESC,
				COUNT(CASE WHEN g.scoreHome IS NOT NULL THEN 1 ELSE NULL END) DESC
				)	t,
				(SELECT @homerownum := 0) r       
				) HomeRank on HomeRank.teamID=teamHome.ID
				LEFT OUTER JOIN (
				SELECT @awayrownum:=@awayrownum + 1 as rank,
				TeamName,
				teamID
				FROM 
				(
				SELECT
				th.teamname AS TeamName,
				th.ID teamID
				FROM
				teamhistory AS th
				LEFT OUTER JOIN games AS g ON th.ID = g.teamHomeID OR th.ID = g.teamAwayID
				LEFT OUTER JOIN leagues AS l on l.ID = th.leagueID
				LEFT OUTER JOIN teams t on t.ID=th.teamID
				LEFT OUTER JOIN sessions s on s.ID=th.sessionID
				WHERE
				l.alias = '".$leagueAlias."'
				AND g.sessionID = '".$sessionID."'
				AND g.GameTypeID=1
				GROUP BY
				th.teamName,
				th.leagueID,
				th.teamid,
				g.SessionID
				ORDER BY
				(COUNT(CASE WHEN g.teamHomeID=th.id THEN CASE WHEN g.scoreHome > g.scoreAway THEN 1 ELSE NULL END ELSE CASE WHEN  g.scoreHome < g.scoreAway THEN 1 ELSE NULL END END) * 2)+(COUNT(CASE WHEN g.scoreHome=g.scoreAway THEN 1 ELSE NULL END))+(COUNT(CASE WHEN g.OTL=1 THEN CASE WHEN g.teamHomeID=th.id THEN CASE WHEN g.scoreAway > g.scoreHome THEN 1 ELSE NULL END END END ) + 
				COUNT(CASE WHEN g.OTL=1 THEN CASE WHEN g.teamAwayID=th.id THEN CASE WHEN g.scoreHome > g.scoreAway THEN 1 ELSE NULL END END END )) DESC,
				SUM(CASE WHEN g.teamAwayID<>th.ID THEN scoreAway ELSE scoreHome END),
				SUM(CASE WHEN g.teamHomeID=th.ID THEN scoreHome ELSE scoreAway END) DESC,
				COUNT(CASE WHEN g.scoreHome IS NOT NULL THEN 1 ELSE NULL END) DESC
				)	t,
				(SELECT @awayrownum := 0) r       
				) AwayRank on AwayRank.teamID=teamAway.ID
				WHERE
				g.gameTypeID='".$round."'
				AND l.alias='".$leagueAlias."'
				AND g.sessionID='".$sessionID."'
				ORDER BY HomeRank ASC";
		return $this->db->fetchAll($sql);
	}
	

	public function checkTeamCaptainAuth($userID, $teamID){
		$sql = "SELECT count(*) as count
				FROM teamcaptainhistory
				WHERE userID='".$userID."'
				AND teamID='".$teamID."'
				AND statusID=1";

		return $this->db->fetchAll($sql);
	}
	
	public function checkTeamHasCaptain($teamID){
		$sql = "SELECT count(*) as count
				FROM teamcaptainhistory
				WHERE teamID='".$teamID."'
				AND statusID=1";

		return $this->db->fetchAll($sql);
	}
	
	public function getTeamsPlayedByGameID($gameID){
		$sql = 
			"SELECT thHome.ID homeTeamHistoryID, thHome.teamName homeTeamName, tHome.alias homeAlias,
			thAway.ID awayTeamHistoryID, thAway.teamName awayTeamName, tAway.alias awayAlias
			FROM games g
			INNER JOIN teamhistory thHome on thHome.ID=g.teamHomeID
			INNER JOIN teams tHome on tHome.ID=thHome.teamID
			INNER JOIN teamhistory thAway on thAway.ID=g.teamAwayID
			INNER JOIN teams tAway on tAway.ID=thAway.teamID
			WHERE g.ID='".$gameID."'";
		
		return $this->db->fetchAll($sql);
	}

	public function getCheckedInPlayers($gameID, $teamHistoryID){
		$sql = "SELECT pr.ID as ID,
				p.firstName,
				p.lastName,
				CASE WHEN pr.jerseyNumber IS NOT NULL AND pr.gameID='".$gameID."'
				THEN pr.jerseyNumber 
				ELSE ph.jerseyNumber END jerseyNumber,
				CASE WHEN pr.ID IS NOT NULL  AND pr.gameID='".$gameID."'
				THEN 1
				ELSE 0 END isPlayerCheckedIn
				FROM players p
				LEFT OUTER JOIN playerhistory ph on ph.playerID=p.ID
				LEFT OUTER JOIN playerrosters pr on pr.playerHistoryID=ph.ID
				WHERE ph.teamHistoryID='".$teamHistoryID."'
				AND ph.statusID=1
				ORDER BY jerseyNumber";
		
		return $this->db->fetchAll($sql);
	}
	public function getScoreOverview($gameID){
		$sql = "SELECT th.teamName,t.alias teamAlias,
				COUNT(CASE WHEN ga.period=1 THEN 1 ELSE NULL END) Period1Goals,
				COUNT(CASE WHEN ga.period=2 THEN 1 ELSE NULL END) Period2Goals,
				COUNT(CASE WHEN ga.period=3 THEN 1 ELSE NULL END) Period3Goals,
				COUNT(CASE WHEN ga.period=4 THEN 1 ELSE NULL END) PeriodOTGoals,
				CASE WHEN th.ID=g.teamHomeID THEN scoreHome ELSE scoreAway END TotalGoals
				FROM games g
				LEFT OUTER JOIN teamhistory th ON (th.ID=g.teamHomeID OR th.ID=g.teamAwayID)
				LEFT OUTER JOIN playerhistory ph ON ph.teamHistoryID=th.ID
				LEFT OUTER JOIN playerrosters pr ON pr.playerHistoryID=ph.ID AND pr.gameID='".$gameID."'
				LEFT OUTER JOIN gameactivities ga ON ga.skaterID=pr.ID and ga.gameActivityTypeID in (SELECT ID FROM gameactivitytypes WHERE type=1)
				LEFT OUTER JOIN gameactivitytypes gat ON gat.ID=ga.gameActivityTypeID
				LEFT OUTER JOIN teams t ON t.ID=th.teamID
				WHERE g.ID='".$gameID."'
				GROUP BY th.teamName,ph.teamHistoryID,t.alias,th.ID,g.teamHomeID,scoreHome,scoreAway";
		
		return $this->db->fetchAll($sql);
		
	}

	public function getScoreSummary($gameID, $teamHistoryID, $period){
		$sql = "SELECT th.teamName,
				t.alias teamAlias, 
				gat.type, 
				gat.PIM, 
				CONCAT(skaterp.firstName, ' ' ,skaterp.lastName) Scorer, 
				skaterpr.ID ScorerID, 
				CONCAT(assist1p.firstName, ' ' ,assist1p.lastName) Assist1, 
				assist1pr.ID Assist1ID,
				CONCAT(assist2p.firstName, ' ' ,assist2p.lastName) Assist2, 
				assist2pr.ID Assist2ID,
				gat.description 
				GoalType, 
				ga.emptyNet, 
				ga.time
                FROM gameactivities ga
                
                INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
                
                INNER JOIN playerrosters skaterpr on ga.skaterID=skaterpr.ID
                INNER JOIN playerhistory skaterph on skaterph.ID=skaterpr.playerHistoryID
                INNER JOIN players skaterp on skaterp.ID=skaterph.playerID
                INNER JOIN playerhistory ph on ph.ID=skaterpr.playerHistoryID
                INNER JOIN teamhistory th on th.ID=ph.teamHistoryID
                INNER JOIN teams t on t.ID=th.teamID
                
                LEFT OUTER JOIN playerrosters assist1pr on assist1pr.ID=ga.assist1ID
                LEFT OUTER JOIN playerhistory assist1ph on assist1ph.ID=assist1pr.playerHistoryID
                LEFT OUTER JOIN players assist1p on assist1p.ID=assist1ph.playerID
                
                LEFT OUTER JOIN playerrosters assist2pr on assist2pr.ID=ga.assist2ID
                LEFT OUTER JOIN playerhistory assist2ph on assist2ph.ID=assist2pr.playerHistoryID
                LEFT OUTER JOIN players assist2p on assist2p.ID=assist2ph.playerID
                
                WHERE skaterpr.gameID='".$gameID."'
                AND ph.teamHistoryID='".$teamHistoryID."'
                AND ga.period='".$period."'
				ORDER BY ga.time DESC";
		
		return $this->db->fetchAll($sql);
		
	}
	
	public function getTeamScoringByGame($gameID, $teamHistoryID){
		$sql = 
			"SELECT th.teamName,p.firstName,p.lastName,p.ID playerID,
			IFNULL(Goals.Goals,0) G,
			IFNULL(Assist1.Assists,0)+IFNULL(Assist2.Assists,0) A,
			IFNULL(Goals.Goals,0)+IFNULL(Assist1.Assists,0)+IFNULL(Assist2.Assists,0) PTS,
			IFNULL(PIMs.PIM,0) PIM
			FROM playerhistory ph
			INNER JOIN playerrosters pr on pr.playerHistoryID = ph.ID
			INNER JOIN players p on p.ID=ph.playerID
			INNER JOIN teamhistory th on th.ID=ph.teamHistoryID
			LEFT OUTER JOIN gameactivities ga on (pr.ID=ga.skaterID or (pr.ID=ga.skaterID OR pr.ID=ga.assist1ID OR pr.ID=ga.assist2ID))
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID,
				COUNT(CASE WHEN (ga.skaterID=pr.ID)AND gat.type=1 THEN 1 ELSE null END) Goals
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.skaterID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				GROUP BY pr.ID) Goals ON Goals.playerRosterID = pr.ID
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID,
				COUNT(CASE WHEN (ga.assist1ID=pr.ID)AND gat.type=1 THEN 1 ELSE null END) Assists
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.assist1ID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				GROUP BY pr.ID) Assist1 ON Assist1.playerRosterID = pr.ID
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID,
				COUNT(CASE WHEN (ga.assist2ID=pr.ID)AND gat.type=1 THEN 1 ELSE null END) Assists
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.assist2ID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				GROUP BY pr.ID) Assist2 ON Assist2.playerRosterID = pr.ID
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID,
				SUM(CASE WHEN (ga.skaterID=pr.ID)AND gat.type=2 THEN gat.PIM ELSE null END) PIM
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.skaterID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				GROUP BY pr.ID) PIMs ON PIMs.playerRosterID = pr.ID
			WHERE ph.teamHistoryID='".$teamHistoryID."'
			and gameID='".$gameID."'
			GROUP BY p.ID
			ORDER BY PTS DESC, G DESC, A DESC, PIM DESC";
		
		return $this->db->fetchAll($sql);
	}
	
	public function getOverallTeamScoring($teamHistoryID){
		$sql = "SELECT th.teamName,p.firstName,p.lastName,p.ID playerID, ph.jerseyNumber,
			IFNULL(GP.GP,0) GP,
			IFNULL(Goals.Goals,0) G,
			IFNULL(Assist1.Assists,0)+IFNULL(Assist2.Assists,0) A,
			IFNULL(Goals.Goals,0)+IFNULL(Assist1.Assists,0)+IFNULL(Assist2.Assists,0) PTS,
			IFNULL(PIMs.PIM,0) PIM
			FROM playerhistory ph
			INNER JOIN playerrosters pr on pr.playerHistoryID = ph.ID
			INNER JOIN players p on p.ID=ph.playerID
			INNER JOIN teamhistory th on th.ID=ph.teamHistoryID
			LEFT OUTER JOIN gameactivities ga on (pr.ID=ga.skaterID or (pr.ID=ga.skaterID OR pr.ID=ga.assist1ID OR pr.ID=ga.assist2ID))
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				COUNT(CASE WHEN (ga.skaterID=pr.ID)AND gat.type=1 THEN 1 ELSE null END) Goals
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.skaterID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				INNER JOIN players p on p.ID=ph.playerID
				WHERE ph.teamHistoryID=".$teamHistoryID."
				GROUP BY ph.ID) Goals ON Goals.playerHistoryID = ph.ID
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				COUNT(CASE WHEN (ga.assist1ID=pr.ID)AND gat.type=1 THEN 1 ELSE null END) Assists
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.assist1ID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				INNER JOIN players p on p.ID=ph.playerID
				WHERE ph.teamHistoryID=".$teamHistoryID."
				GROUP BY ph.ID) Assist1 ON Assist1.playerHistoryID = ph.ID
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				COUNT(CASE WHEN (ga.assist2ID=pr.ID)AND gat.type=1 THEN 1 ELSE null END) Assists
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.assist2ID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				INNER JOIN players p on p.ID=ph.playerID
				WHERE ph.teamHistoryID=".$teamHistoryID."
				GROUP BY ph.ID) Assist2 ON Assist2.playerHistoryID = ph.ID
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				SUM(CASE WHEN (ga.skaterID=pr.ID)AND gat.type=2 THEN gat.PIM ELSE null END) PIM
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.skaterID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				INNER JOIN players p on p.ID=ph.playerID
				WHERE ph.teamHistoryID=".$teamHistoryID."
				GROUP BY ph.ID) PIMs ON PIMs.playerHistoryID = ph.ID
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				COUNT(CASE WHEN (pr.playerHistoryID = ph.ID) THEN 1 ELSE null END) GP
				FROM playerrosters pr
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				INNER JOIN players p on p.ID=ph.playerID
				WHERE ph.teamHistoryID=".$teamHistoryID."
				GROUP BY ph.ID) GP ON  GP.playerHistoryID = ph.ID
			WHERE ph.teamHistoryID=".$teamHistoryID."
			GROUP BY p.ID
			ORDER BY PTS DESC, G DESC, A DESC, PIM DESC, GP DESC";
		return $this->db->fetchAll($sql);
	}
	//Most Penalties by session
	public function getGoons($sessionID){ 
		$sql = "SELECT th.teamName,t.alias,p.firstName,p.lastName,p.ID playerID,
			IFNULL(GP.GP,0) GP,
			IFNULL(PIMs.PIM,0) PIM
			FROM playerhistory ph
			INNER JOIN playerrosters pr on pr.playerHistoryID = ph.ID
			INNER JOIN players p on p.ID=ph.playerID
			INNER JOIN teamhistory th on th.ID=ph.teamHistoryID
			INNER JOIN teams t on  t.ID = th.teamID
			LEFT OUTER JOIN gameactivities ga on (pr.ID=ga.skaterID or (pr.ID=ga.skaterID OR pr.ID=ga.assist1ID OR pr.ID=ga.assist2ID))
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				SUM(CASE WHEN (ga.skaterID=pr.ID)AND gat.type=2 THEN gat.PIM ELSE null END) PIM
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.skaterID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				GROUP BY ph.ID) PIMs ON PIMs.playerHistoryID = ph.ID
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				COUNT(CASE WHEN (pr.playerHistoryID = ph.ID) THEN 1 ELSE null END) GP
				FROM playerrosters pr
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				GROUP BY ph.ID) GP ON  GP.playerHistoryID = ph.ID
			WHERE th.sessionID = ".$sessionID."
			GROUP BY p.ID
			ORDER BY PIM DESC, GP ASC
			LIMIT 0,10";
		return $this->db->fetchAll($sql);
	}
	//Most Penalties by session & league
	public function getGoonsByLeague($sessionID, $leagueID){
		$sql = "SELECT th.teamName,t.alias,p.firstName,p.lastName,p.ID playerID,
			IFNULL(GP.GP,0) GP,
			IFNULL(PIMs.PIM,0) PIM
			FROM playerhistory ph
			INNER JOIN playerrosters pr on pr.playerHistoryID = ph.ID
			INNER JOIN players p on p.ID=ph.playerID
			INNER JOIN teamhistory th on th.ID=ph.teamHistoryID
			INNER JOIN teams t on  t.ID = th.teamID
			LEFT OUTER JOIN gameactivities ga on (pr.ID=ga.skaterID or (pr.ID=ga.skaterID OR pr.ID=ga.assist1ID OR pr.ID=ga.assist2ID))
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				SUM(CASE WHEN (ga.skaterID=pr.ID)AND gat.type=2 THEN gat.PIM ELSE null END) PIM
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.skaterID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				GROUP BY ph.ID) PIMs ON PIMs.playerHistoryID = ph.ID
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				COUNT(CASE WHEN (pr.playerHistoryID = ph.ID) THEN 1 ELSE null END) GP
				FROM playerrosters pr
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				GROUP BY ph.ID) GP ON  GP.playerHistoryID = ph.ID
			WHERE th.leagueID = ".$leagueID."
			AND th.sessionID = ".$sessionID."
			GROUP BY p.ID
			ORDER BY PIM DESC, GP ASC
			LIMIT 0,10";
		return $this->db->fetchAll($sql);
	}
	//Get the players with the most points by Session
	public function getMostPtsPlayers($sessionID){
		$sql = "SELECT th.teamName,t.alias,p.firstName,p.lastName,p.ID playerID,
			IFNULL(GP.GP,0) GP,
			IFNULL(Goals.Goals,0) G,
			IFNULL(Assist1.Assists,0)+IFNULL(Assist2.Assists,0) A,
			IFNULL(Goals.Goals,0)+IFNULL(Assist1.Assists,0)+IFNULL(Assist2.Assists,0) PTS,
			IFNULL(PIMs.PIM,0) PIM
			FROM playerhistory ph
			INNER JOIN playerrosters pr on pr.playerHistoryID = ph.ID
			INNER JOIN players p on p.ID=ph.playerID
			INNER JOIN teamhistory th on th.ID=ph.teamHistoryID
			INNER JOIN teams t on  t.ID = th.teamID
			LEFT OUTER JOIN gameactivities ga on (pr.ID=ga.skaterID or (pr.ID=ga.skaterID OR pr.ID=ga.assist1ID OR pr.ID=ga.assist2ID))
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				COUNT(CASE WHEN (ga.skaterID=pr.ID)AND gat.type=1 THEN 1 ELSE null END) Goals
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.skaterID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				GROUP BY ph.ID) Goals ON Goals.playerHistoryID = ph.ID
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				COUNT(CASE WHEN (ga.assist1ID=pr.ID)AND gat.type=1 THEN 1 ELSE null END) Assists
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.assist1ID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				GROUP BY ph.ID) Assist1 ON Assist1.playerHistoryID = ph.ID
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				COUNT(CASE WHEN (ga.assist2ID=pr.ID)AND gat.type=1 THEN 1 ELSE null END) Assists
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.assist2ID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				GROUP BY ph.ID) Assist2 ON Assist2.playerHistoryID = ph.ID
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				SUM(CASE WHEN (ga.skaterID=pr.ID)AND gat.type=2 THEN gat.PIM ELSE null END) PIM
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.skaterID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				GROUP BY ph.ID) PIMs ON PIMs.playerHistoryID = ph.ID
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				COUNT(CASE WHEN (pr.playerHistoryID = ph.ID) THEN 1 ELSE null END) GP
				FROM playerrosters pr
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				GROUP BY ph.ID) GP ON  GP.playerHistoryID = ph.ID
			WHERE th.sessionID = ".$sessionID."
			GROUP BY p.ID
			ORDER BY PTS DESC, G DESC, A DESC, GP ASC
			LIMIT 0,10";
		return $this->db->fetchAll($sql);
	}
	//Get the players with the most points by Session & League
	public function getMostPtsPlayersByLeague($sessionID, $leagueID){
		$sql = "SELECT th.teamName,t.alias,p.firstName,p.lastName,p.ID playerID,
			IFNULL(GP.GP,0) GP,
			IFNULL(Goals.Goals,0) G,
			IFNULL(Assist1.Assists,0)+IFNULL(Assist2.Assists,0) A,
			IFNULL(Goals.Goals,0)+IFNULL(Assist1.Assists,0)+IFNULL(Assist2.Assists,0) PTS,
			IFNULL(PIMs.PIM,0) PIM
			FROM playerhistory ph
			INNER JOIN playerrosters pr on pr.playerHistoryID = ph.ID
			INNER JOIN players p on p.ID=ph.playerID
			INNER JOIN teamhistory th on th.ID=ph.teamHistoryID
			INNER JOIN teams t on  t.ID = th.teamID
			LEFT OUTER JOIN gameactivities ga on (pr.ID=ga.skaterID or (pr.ID=ga.skaterID OR pr.ID=ga.assist1ID OR pr.ID=ga.assist2ID))
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				COUNT(CASE WHEN (ga.skaterID=pr.ID)AND gat.type=1 THEN 1 ELSE null END) Goals
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.skaterID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				GROUP BY ph.ID) Goals ON Goals.playerHistoryID = ph.ID
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				COUNT(CASE WHEN (ga.assist1ID=pr.ID)AND gat.type=1 THEN 1 ELSE null END) Assists
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.assist1ID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				GROUP BY ph.ID) Assist1 ON Assist1.playerHistoryID = ph.ID
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				COUNT(CASE WHEN (ga.assist2ID=pr.ID)AND gat.type=1 THEN 1 ELSE null END) Assists
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.assist2ID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				GROUP BY ph.ID) Assist2 ON Assist2.playerHistoryID = ph.ID
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				SUM(CASE WHEN (ga.skaterID=pr.ID)AND gat.type=2 THEN gat.PIM ELSE null END) PIM
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.skaterID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				GROUP BY ph.ID) PIMs ON PIMs.playerHistoryID = ph.ID
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				COUNT(CASE WHEN (pr.playerHistoryID = ph.ID) THEN 1 ELSE null END) GP
				FROM playerrosters pr
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				GROUP BY ph.ID) GP ON  GP.playerHistoryID = ph.ID
			WHERE th.leagueID = ".$leagueID."
			AND th.sessionID = ".$sessionID."
			GROUP BY p.ID
			ORDER BY PTS DESC, G DESC, A DESC, GP ASC
			LIMIT 0,10";
		return $this->db->fetchAll($sql);
	}
	//Get the players with the most game winning goals in OT by Session & League
	public function getMostOTGoals($sessionID, $leagueID){
		$sql = "SELECT th.teamName,t.alias,p.firstName,p.lastName,p.ID playerID,l.name as league,
			IFNULL(GP.GP,0) GP,
			IFNULL(Goals.Goals,0) G
			FROM playerhistory ph
			INNER JOIN playerrosters pr on pr.playerHistoryID = ph.ID
			INNER JOIN players p on p.ID=ph.playerID
			INNER JOIN teamhistory th on th.ID=ph.teamHistoryID
			INNER JOIN teams t on  t.ID = th.teamID
			INNER JOIN leagues l on l.ID = th.leagueID
			LEFT OUTER JOIN gameactivities ga on (pr.ID=ga.skaterID or (pr.ID=ga.skaterID OR pr.ID=ga.assist1ID OR pr.ID=ga.assist2ID))
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				COUNT(CASE WHEN (ga.skaterID=pr.ID)AND gat.type=1 THEN 1 ELSE null END) Goals
				FROM gameactivities ga
				INNER JOIN playerrosters pr on pr.ID=ga.skaterID
				INNER JOIN gameactivitytypes gat on gat.ID=ga.gameActivityTypeID
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				WHERE ga.period=4
				GROUP BY ph.ID) Goals ON Goals.playerHistoryID = ph.ID
			LEFT OUTER JOIN (
				SELECT pr.ID playerRosterID, ph.ID playerHistoryID,
				COUNT(CASE WHEN (pr.playerHistoryID = ph.ID) THEN 1 ELSE null END) GP
				FROM playerrosters pr
				INNER JOIN playerhistory ph on ph.ID = pr.playerHistoryID
				GROUP BY ph.ID) GP ON  GP.playerHistoryID = ph.ID
			WHERE th.sessionID = ".$sessionID."
			AND l.ID = ".$leagueID."
			AND Goals<>0
			GROUP BY p.ID
			ORDER BY G DESC,GP ASC
			LIMIT 0,10";
		return $this->db->fetchAll($sql);
	}
	
}

?>