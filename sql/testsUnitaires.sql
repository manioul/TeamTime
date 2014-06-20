DELIMITER |
DROP TABLE IF EXISTS TESTS_UNITAIRES|
CREATE TABLE TESTS_UNITAIRES(
	id INT(11) auto_increment,
	timestamp TIMESTAMP,
	procédure VARCHAR(255),
	resultat BOOLEAN,
	PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8|
DROP PROCEDURE IF EXISTS testsUnitaires|
CREATE PROCEDURE testsUnitaires()
BEGIN
END
|
DROP PROCEDURE IF EXISTS demiCycleTest|
CREATE PROCEDURE demiCycleTest(IN dat DATE, centr VARCHAR(50), IN tea VARCHAR(10), debutAttendu DATE, finAttendu DATE, crAttendu INT(11))
BEGIN
	DECLARE debutDemiCycle DATE;
	DECLARE finDemiCycle DATE;
	DECLARE codeRetour INT(11);
	CALL demiCycle(dat, centr, tea, debutDemiCycle, finDemiCycle, codeRetour);
	IF (debutDemiCycle = debutAttendu AND finDemiCycle = finAttendu AND codeRetour = crAttendu) OR (debutAttendu IS NULL AND finAttendu IS NULL AND debutDemiCycle IS NULL AND finDemiCycle IS NULL) THEN
		INSERT INTO TESTS_UNITAIRES
		VALUES
		(NULL, NOW(), 'demiCycle', 1);
	ELSE
		INSERT INTO TESTS_UNITAIRES
		VALUES
		(NULL, NOW(), 'demiCycle', 0);
	END IF;
END
|
DROP PROCEDURE IF EXISTS demiCycleTestGo|
CREATE PROCEDURE demiCycleTestGo()
BEGIN
	CALL demiCycleTest('2014-01-15', 'athis', '9e', '2014-01-15', '2014-01-17', 0);
	CALL demiCycleTest('2014-01-16', 'athis', '9e', '2014-01-15', '2014-01-17', 0);
	CALL demiCycleTest('2014-01-17', 'athis', '9e', '2014-01-15', '2014-01-17', 0);
	CALL demiCycleTest('2014-01-18', 'athis', '9e', NULL, NULL, 0);
	CALL demiCycleTest('2014-01-19', 'athis', '9e', NULL, NULL, 0);
	CALL demiCycleTest('2014-01-20', 'athis', '9e', NULL, NULL, 0);
END
|
DROP PROCEDURE IF EXISTS dateLimiteCongesTest|
CREATE PROCEDURE dateLimiteCongesTest(IN year INT(11), IN centr VARCHAR(50), IN dlAttendue DATE)
BEGIN
	DECLARE dateLimite DATE;
	DECLARE codeRetour INT(11);
	CALL dateLimiteConges(year, centr, dateLimite, codeRetour);
	IF dateLimite = dlAttendue AND codeRetour = 0 THEN
		INSERT INTO TESTS_UNITAIRES
		VALUES
		(NOW(), 'dateLimiteConges', 1);
	ELSE
		INSERT INTO TESTS_UNITAIRES
		VALUES
		(NOW(), 'dateLimiteConges', 0);
	END IF;
END
|
DROP PROCEDURE IF EXISTS dateLimiteCongesTestGo|
CREATE PROCEDURE dateLimiteCongesTestGo()
BEGIN
	CALL dateLimiteCongesTest(2012, 'athis', '2013-03-31');
	CALL dateLimiteCongesTest(2013, 'athis', '2014-04-07');
END
|
DROP PROCEDURE IF EXISTS addAffectationTest|
CREATE PROCEDURE addAffectationTest(IN uid_ SMALLINT(6), IN centr VARCHAR(50) , IN tea VARCHAR(10) , IN grad VARCHAR(64) , IN debut DATE , IN fin DATE)
BEGIN
	DECLARE codeRetour INT(11);
	DECLARE currentBeginning, currentEnd, prevBeginning, prevEnd, nextBeginning, nextEnd DATE;
	DECLARE currentGrade, prevGrade, nextGrade, currentCentre, prevCentre, nextCentre, currentTeam, prevTeam, nextTeam VARCHAR(64);

	SELECT centre, team, grade, beginning, end
	INTO prevCentre, prevTeam, prevGrade, prevBeginning, prevEnd
	FROM TBL_AFFECTATION
	WHERE uid = uid_
	AND beginning < debut
	AND end >= beginning
	ORDER BY beginning DESC
	LIMIT 0, 1;

	CALL addAffectation(uid_, centr, tea, grad, debut, fin, codeRetour );

	SELECT centre, team, grade, beginning, end
	INTO currentCentre, currentTeam, currentGrade, currentBeginning, currentEnd
	FROM TBL_AFFECTATION
	WHERE uid = uid_
	AND beginning = debut
	AND end = fin;
END
|
DROP PROCEDURE IF EXISTS addAffectationTestGo|
CREATE PROCEDURE addAffectationTestGo()
BEGIN
END
|
DROP PROCEDURE IF EXISTS addDispoTest|
CREATE PROCEDURE addDispoTest(IN uid_ SMALLINT(6), IN dat DATE, IN disponibilite VARCHAR(16), IN oldDisponibilite VARCHAR(16), IN perequation BOOLEAN, IN centr VARCHAR(50), IN tea VARCHAR(10), codeRetourAttendu INT(11))
BEGIN
END
|
DELIMITER ;
