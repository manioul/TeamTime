DROP TABLE IF EXISTS TBL_MESSAGES_SYSTEME;
CREATE TABLE TBL_MESSAGES_SYSTEME (
	mid int(11) NOT NULL AUTO_INCREMENT,
	utilisateur VARCHAR(63) NOT NULL,
	catégorie set('DEBUG','INFO','ERREUR','LOG','USER') NOT NULL DEFAULT 'USER',
	-- La catégorie USER est destinée à afficher un message à l'utilisateur
	appelant VARCHAR(64) NOT NULL,
	short tinytext NOT NULL,
	message text NOT NULL,
	contexte text NOT NULL,
	timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (mid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DELIMITER |
DROP PROCEDURE IF EXISTS messageSystem|
CREATE PROCEDURE messageSystem( IN msg TEXT , IN cat VARCHAR(64) , IN app VARCHAR(64) , IN court TINYTEXT , IN context TEXT )
BEGIN
	IF cat IS NULL THEN
		SET cat = 'DEBUG';
	END IF;
	IF app IS NULL THEN
		SET app = 'unknown';
	END IF;
	IF court IS NULL THEN
		SET court = 'unknown';
	END IF;
	IF context IS NULL THEN
		SET context = 'unknown';
	END IF;

	INSERT INTO TBL_MESSAGES_SYSTEME
	(mid, utilisateur, catégorie, appelant, short, message, contexte)
	VALUES
	(NULL, CURRENT_USER(), cat, app, court, msg, context); 
END
|

DELIMITER ;
