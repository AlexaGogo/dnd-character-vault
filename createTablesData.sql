SET DEFINE OFF;

-- CREATE TABLE STATEMENTS

CREATE TABLE DM
(
    dmID int,
    dmName char(100) NOT NULL,
    PRIMARY KEY (dmID)
);

CREATE TABLE GameDMdBy
(
    gameID int,
    gameName char(100) NOT NULL,
    since date,
    dmID int,
    PRIMARY KEY (gameID),
    FOREIGN KEY (dmID) REFERENCES DM(dmID)
);

CREATE TABLE Player
(
    playerID int,
    playerName char(100) NOT NULL,
    PRIMARY KEY (playerID)
);

CREATE TABLE PlayedBy
(
    playerID int,
    gameID int,
    PRIMARY KEY (playerID, gameID),
    FOREIGN KEY (playerID) REFERENCES Player(playerID),
    FOREIGN KEY (gameID) REFERENCES GameDMdBy(gameID)
);

CREATE TABLE Race
(
    raceName char(100),
    raceDesc char(255),
    PRIMARY KEY (raceName),
    UNIQUE (raceDesc)
);

CREATE TABLE XPToLevel
(
    totalXP int,
    lv int NOT NULL,
    PRIMARY KEY (totalXP)
);

CREATE TABLE IsEncumbered
(
    weightLimit int,
    currentWeight int DEFAULT 0,
    overencumber number,
    PRIMARY KEY (weightLimit, currentWeight)
);

CREATE TABLE Chr
(
    charID int,
    playerID int,
    charName char(100) NOT NULL,
    dexterity int,
    constitution int,
    intelligence int,
    strength int,
    charisma int,
    wisdom int,
    weightLimit int,
    currentWeight int DEFAULT 0,
    totalXP int DEFAULT 0,
    raceName char(100) NOT NULL,
    PRIMARY KEY (charID, playerID),
    FOREIGN KEY (playerID) REFERENCES Player(PlayerID),
    FOREIGN KEY (totalXP) REFERENCES XPToLevel(totalXp),
    FOREIGN KEY (weightLimit, currentWeight) REFERENCES IsEncumbered(weightLimit, currentWeight),
    FOREIGN KEY (raceName) REFERENCES Race(raceName)
);

CREATE TABLE Class
(
    className char(100),
    classDesc char(255),
    PRIMARY KEY (className),
    UNIQUE (classDesc)
);

CREATE TABLE AssignedTo
(
    charID int,
    playerID int,
    className char(100),
    PRIMARY KEY (className, charID, playerID),
    FOREIGN KEY (className) REFERENCES Class(className),
    FOREIGN KEY (playerID, charID) REFERENCES Chr(playerID, charID)
);

CREATE TABLE StatusEffect
(
    effectName char(100),
    duration real DEFAULT 0,
    PRIMARY KEY (effectName)
);

CREATE TABLE AfflictedBy
(
    charID int,
    playerID int,
    effectName char(100),
    startTime timestamp,
    endTime timestamp,
    PRIMARY KEY (playerID, charID, effectName),
    FOREIGN KEY (playerID, charID) REFERENCES Chr(playerID, charID),
    FOREIGN KEY (effectName) REFERENCES StatusEffect(effectName)
);

CREATE TABLE Item
(
    itemName char(100),
    requirements char(255),
    itemDesc char(255),
    itemValue real,
    PRIMARY KEY (itemName),
    UNIQUE (itemDesc)
);

CREATE TABLE IsWeaponRanged
(
    needsAmmo number,
    ranged number,
    PRIMARY KEY (needsAmmo)
);

CREATE TABLE Weapon
(
    itemName char(100),
    damage int,
    needsAmmo number,
    PRIMARY KEY (itemName),
    FOREIGN KEY (itemName) REFERENCES Item(itemName),
    FOREIGN KEY (needsAmmo) REFERENCES IsWeaponRanged(needsAmmo)
);

CREATE TABLE IsArmourForStealth
(
    armourType char(100),
    stealthDisadv number,
    PRIMARY KEY (armourType)
);

CREATE TABLE Armour
(
    itemName char(100),
    armourType char(100),
    PRIMARY KEY (itemName),
    FOREIGN KEY (itemName) REFERENCES Item(itemName),
    FOREIGN KEY (armourType) REFERENCES IsArmourForStealth(armourType)
);

CREATE TABLE Tool
(
    itemName char(100),
    PRIMARY KEY (itemName),
    FOREIGN KEY (itemName) REFERENCES Item(itemName)
);

CREATE TABLE Owns
(
    itemName char(100),
    charID int,
    playerID int,
    quantity int,
    PRIMARY KEY (itemName, playerID, charID),
    FOREIGN KEY (itemName) REFERENCES Item(itemName),
    FOREIGN KEY (playerID, charID) REFERENCES Chr(playerID, charID)
);

CREATE TABLE Skill
(
    skillName char(100),
    skillDesc char(255),
    skillType char(100),
    targets char(255),
    active number NOT NULL,
    PRIMARY KEY (skillName),
    UNIQUE (skillDesc)
);

CREATE TABLE Attack
(
    skillName char(100),
    damage int,
    PRIMARY KEY (SkillName),
    FOREIGN KEY (SkillName) REFERENCES Skill(SkillName)
);

CREATE TABLE Feature
(
    skillname char(100),
    PRIMARY KEY (SkillName),
    FOREIGN KEY (SkillName) REFERENCES Skill(SkillName)
);

CREATE TABLE Spell
(
    skillName char(100),
    manaCost int,
    PRIMARY KEY (SkillName),
    FOREIGN KEY (SkillName) REFERENCES Skill(SkillName)
);

CREATE TABLE Has
(
    skillName char(100),
    charID int,
    playerID int,
    quantity int,
    PRIMARY KEY (skillName, playerID, charID),
    FOREIGN KEY (skillName) REFERENCES Skill(skillName),
    FOREIGN KEY (playerID, charID) REFERENCES Chr(playerID, charID)
);

-- INSERT STATEMENTS
INSERT INTO DM
VALUES
    (10000, 'Jane Doe');
INSERT INTO DM
VALUES
    (10001, 'Marc Garneau');
INSERT INTO DM
VALUES
    (10002, 'Gord Downie');
INSERT INTO DM
VALUES
    (10003, 'Hidetaka Miyazaki');
INSERT INTO DM
VALUES
    (10004, 'Yuji Horii');
INSERT INTO GameDMdBy
VALUES
    (10000, 'The Five Friends', TO_DATE('2023-02-28', 'YYYY-MM-DD'), 10003);
INSERT INTO GameDMdBy
VALUES
    (10001, 'Gun City', TO_DATE('2019-06-10', 'YYYY-MM-DD'), 10004);
INSERT INTO GameDMdBy
VALUES
    (10002, 'Trebuchet Village', TO_DATE('2021-12-25', 'YYYY-MM-DD'), 10003);
INSERT INTO GameDMdBy
VALUES
    (10003, 'The Crazy Wizard', TO_DATE('2015-11-02', 'YYYY-MM-DD'), 10000);
INSERT INTO GameDMdBy
VALUES
    (10004, 'AwesomeLand', TO_DATE('1999-02-22', 'YYYY-MM-DD'), 10002);
INSERT INTO Player
VALUES
    (100000, 'Mitch Hedberg');
INSERT INTO Player
VALUES
    (100001, 'Lu Bu');
INSERT INTO Player
VALUES
    (100002, 'Rachel Smith');
INSERT INTO Player
VALUES
    (100003, 'Cao Cao');
INSERT INTO Player
VALUES
    (100004, 'Guan Yu');
INSERT INTO Player
VALUES
    (100005, 'Rebecca Washington');
INSERT INTO Player
VALUES
    (100006, 'Cecil McKinley');
INSERT INTO PlayedBy
VALUES
    (100006, 10000);
INSERT INTO PlayedBy
VALUES
    (100004, 10000);
INSERT INTO PlayedBy
VALUES
    (100003, 10000);
INSERT INTO PlayedBy
VALUES
    (100000, 10000);
INSERT INTO PlayedBy
VALUES
    (100001, 10000);
INSERT INTO PlayedBy
VALUES
    (100000, 10001);
INSERT INTO PlayedBy
VALUES
    (100003, 10001);
INSERT INTO PlayedBy
VALUES
    (100004, 10001);
INSERT INTO PlayedBy
VALUES
    (100006, 10002);
INSERT INTO PlayedBy
VALUES
    (100005, 10002);
INSERT INTO PlayedBy
VALUES
    (100004, 10002);
INSERT INTO PlayedBy
VALUES
    (100003, 10003);
INSERT INTO PlayedBy
VALUES
    (100002, 10003);
INSERT INTO PlayedBy
VALUES
    (100001, 10003);
INSERT INTO PlayedBy
VALUES
    (100000, 10004);
INSERT INTO PlayedBy
VALUES
    (100005, 10004);
INSERT INTO Race
VALUES
    ('Dragonborn', 'Humanoid dragons.');
INSERT INTO Race
VALUES
    ('Dwarf', 'Bold & hardy.');
INSERT INTO Race
VALUES
    ('Elf', 'Magical graceful people.');
INSERT INTO Race
VALUES
    ('Gnome', 'Tiny & energetic.');
INSERT INTO Race
VALUES
    ('Half-Elf', 'Kind of an elf.');
INSERT INTO Race
VALUES
    ('Halfling', 'Little guys & gals.');
INSERT INTO Race
VALUES
    ('Half-Orc', 'Kind of an orc.');
INSERT INTO Race
VALUES
    ('Human', 'Homo sapiens.');
INSERT INTO Race
VALUES
    ('Tiefling', 'Devilish.');
INSERT INTO XPToLevel
VALUES
    (253665, 17);
INSERT INTO XPToLevel
VALUES
    (120, 1);
INSERT INTO XPToLevel
VALUES
    (40892, 8);
INSERT INTO XPToLevel
VALUES
    (115067, 12);
INSERT INTO XPToLevel
VALUES
    (125432, 13);
INSERT INTO XPToLevel
VALUES
    (6547, 5);
INSERT INTO XPToLevel
VALUES
    (23010, 7);
INSERT INTO XPToLevel
VALUES
    (67031, 10);
INSERT INTO XPToLevel
VALUES
    (85441, 11);
INSERT INTO XPToLevel
VALUES
    (2756, 4);
INSERT INTO IsEncumbered
VALUES
    (225, 104, 0);
INSERT INTO IsEncumbered
VALUES
    (120, 157, 1);
INSERT INTO IsEncumbered
VALUES
    (300, 178, 0);
INSERT INTO IsEncumbered
VALUES
    (105, 44, 0);
INSERT INTO IsEncumbered
VALUES
    (165, 39, 0);
INSERT INTO IsEncumbered
VALUES
    (255, 17, 0);
INSERT INTO IsEncumbered
VALUES
    (210, 92, 0);
INSERT INTO IsEncumbered
VALUES
    (210, 119, 0);
INSERT INTO IsEncumbered
VALUES
    (165, 210, 1);
INSERT INTO IsEncumbered
VALUES
    (225, 201, 0);
INSERT INTO Chr
VALUES
    (100000, 100000, 'Bartibus Beetleby', 13, 11, 12, 7, 17, 9, 225, 104, 253665, 'Halfling');
INSERT INTO Chr
VALUES
    (100001, 100000, 'The Smiling Giggler', 14, 12, 15, 9, 7, 13, 120, 157, 120, 'Human');
INSERT INTO Chr
VALUES
    (100002, 100001, 'Crusher McSmashy', 10, 17, 6, 19, 7, 6, 300, 178, 40892, 'Half-Orc');
INSERT INTO Chr
VALUES
    (100003, 100002, 'Kyra Darkblade', 16, 11, 15, 13, 9, 12, 105, 44, 115067, 'Tiefling');
INSERT INTO Chr
VALUES
    (100004, 100002, 'Ynnead Malekith', 19, 12, 16, 9, 11, 14, 165, 39, 125432, 'Elf');
INSERT INTO Chr
VALUES
    (100005, 100003, 'Zhao Ming', 15, 13, 14, 15, 9, 16, 255, 17, 6547, 'Dragonborn');
INSERT INTO Chr
VALUES
    (100006, 100004, 'Sir Thaddeus von Totalerquatsch', 12, 14, 7, 14, 10, 9, 210, 92, 23010, 'Human');
INSERT INTO Chr
VALUES
    (100007, 100005, 'Belac Sprinkles', 14, 7, 4, 5, 20, 8, 210, 119, 67031, 'Gnome');
INSERT INTO Chr
VALUES
    (100008, 100006, 'Martha Aleguzzler', 9, 15, 17, 15, 8, 9, 165, 210, 85441, 'Dwarf');
INSERT INTO Chr
VALUES
    (100009, 100005, 'Patricia Petunia', 7, 18, 16, 15, 19, 4, 225, 201, 2756, 'Halfling');
INSERT INTO Class
VALUES
    ('Barbarian', 'A raging warrior.');
INSERT INTO Class
VALUES
    ('Bard', 'An inspiring magician.');
INSERT INTO Class
VALUES
    ('Cleric', 'A priestly champion.');
INSERT INTO Class
VALUES
    ('Druid', 'A priest of nature.');
INSERT INTO Class
VALUES
    ('Fighter', 'A master of martial combat.');
INSERT INTO Class
VALUES
    ('Monk', 'A master of martial arts.');
INSERT INTO Class
VALUES
    ('Paladin', 'A holy warrior.');
INSERT INTO Class
VALUES
    ('Ranger', 'A warrior of the wilderness.');
INSERT INTO Class
VALUES
    ('Rogue', 'A stealthy scoundrel.');
INSERT INTO Class
VALUES
    ('Sorcerer', 'A gifted spellcaster.');
INSERT INTO Class
VALUES
    ('Warlock', 'A magic wielder with a supernatural bargain.');
INSERT INTO Class
VALUES
    ('Wizard', 'A scholarly magic user.');
INSERT INTO AssignedTo
VALUES
    (100000, 100000, 'Bard');
INSERT INTO AssignedTo
VALUES
    (100001, 100000, 'Bard');
INSERT INTO AssignedTo
VALUES
    (100002, 100001, 'Barbarian');
INSERT INTO AssignedTo
VALUES
    (100003, 100002, 'Warlock');
INSERT INTO AssignedTo
VALUES
    (100004, 100002, 'Ranger');
INSERT INTO AssignedTo
VALUES
    (100005, 100003, 'Paladin');
INSERT INTO AssignedTo
VALUES
    (100006, 100004, 'Fighter');
INSERT INTO AssignedTo
VALUES
    (100007, 100005, 'Sorcerer');
INSERT INTO AssignedTo
VALUES
    (100008, 100006, 'Fighter');
INSERT INTO AssignedTo
VALUES
    (100009, 100005, 'Monk');
INSERT INTO StatusEffect
VALUES
    ('Blinded', 200);
INSERT INTO StatusEffect
VALUES
    ('Charmed', 50);
INSERT INTO StatusEffect
VALUES
    ('Deafened', 75);
INSERT INTO StatusEffect
VALUES
    ('Frightened', 90);
INSERT INTO StatusEffect
VALUES
    ('Invisible', 75);
INSERT INTO AfflictedBy VALUES (100003, 100002, 'Invisible', TO_TIMESTAMP('2023-02-26 13:22:00', 'YYYY-MM-DD HH24:MI:SS.FF'), TO_TIMESTAMP('2023-02-26 13:22:15', 'YYYY-MM-DD HH24:MI:SS.FF'));
INSERT INTO AfflictedBy VALUES (100000, 100000, 'Blinded', TO_TIMESTAMP('2023-02-14 17:02:23', 'YYYY-MM-DD HH24:MI:SS.FF'), TO_TIMESTAMP('2023-02-16 19:02:23', 'YYYY-MM-DD HH24:MI:SS.FF'));
INSERT INTO AfflictedBy VALUES (100000, 100000, 'Frightened', TO_TIMESTAMP('2023-02-14 05:18:41', 'YYYY-MM-DD HH24:MI:SS.FF'), TO_TIMESTAMP('2023-02-14 05:19:41', 'YYYY-MM-DD HH24:MI:SS.FF'));
INSERT INTO AfflictedBy VALUES (100008, 100006, 'Frightened', TO_TIMESTAMP('2023-01-14 12:32:17', 'YYYY-MM-DD HH24:MI:SS.FF'), TO_TIMESTAMP('2023-01-14 12:32:27', 'YYYY-MM-DD HH24:MI:SS.FF'));
INSERT INTO AfflictedBy VALUES (100001, 100000, 'Deafened', TO_TIMESTAMP('2023-01-29 04:47:02', 'YYYY-MM-DD HH24:MI:SS.FF'), TO_TIMESTAMP('2023-02-06 04:47:02', 'YYYY-MM-DD HH24:MI:SS.FF'));
INSERT INTO Item
VALUES
    ('Battleaxe', null, 'A big axe for killing things.', 10);
INSERT INTO Item
VALUES
    ('Warhammer', 'Strength 12+', 'A big hammer that can bludgeon.', 15);
INSERT INTO Item
VALUES
    ('Longbow', 'Dexterity 8+', 'A bow that can shoot things.', 50);
INSERT INTO Item
VALUES
    ('Dull Dagger', null, 'A dull dagger that seems to once hold value…', 0.5);
INSERT INTO Item
VALUES
    ('Spear', 'Dexterity 3+', 'A basic spear.', 5);
INSERT INTO Item
VALUES
    ('Ring Mail', 'Strength 10+', 'Leather armor with heavy rings sewn into it.', 30);
INSERT INTO Item
VALUES
    ('Leather', null, 'Armour made out of leather boiled in oil.', 10);
INSERT INTO Item
VALUES
    ('Chain Mail', 'Strength 13+', 'Armour made of metal rings chained together', 40);
INSERT INTO Item
VALUES
    ('Studded Leather', null, 'Armour made out of leather with studs.', 15);
INSERT INTO Item
VALUES
    ('Basic Shield', null, 'A standard shield.', 10);
INSERT INTO Item
VALUES
    ('Gold Piece', null, 'A single piece of gold. Used to buy things.', 1);
INSERT INTO Item
VALUES
    ('Jewelers Tools', null, 'A jewelry artisans toolkit.', 25);
INSERT INTO Item
VALUES
    ('Disguise Kit', 'Dexterity 3+', 'A kits to disguise yourself with', 25);
INSERT INTO Item
VALUES
    ('Flute', null, 'A neatly crafted wooden flute.', 2);
INSERT INTO Item
VALUES
    ('Basic Alchemy Set', 'Intelligence 5+', 'Standard kit for alchemy', 50);
INSERT INTO Item
VALUES
    ('Painters Tools', null, 'A painters supplies.', 10);
INSERT INTO IsWeaponRanged
VALUES
    (1, 1);
INSERT INTO IsWeaponRanged
VALUES
    (0, 0);
INSERT INTO Weapon
VALUES
    ('Battleaxe', 10, 0);
INSERT INTO Weapon
VALUES
    ('Warhammer', 14, 0);
INSERT INTO Weapon
VALUES
    ('Longbow', 8, 1);
INSERT INTO Weapon
VALUES
    ('Dull Dagger', 1, 0);
INSERT INTO Weapon
VALUES
    ('Spear', 5, 0);
INSERT INTO IsArmourForStealth
VALUES
    ('Heavy Armour', 1);
INSERT INTO IsArmourForStealth
VALUES
    ('Light Armour', 0);
INSERT INTO IsArmourForStealth
VALUES
    ('Heavy Shield', 1);
INSERT INTO IsArmourForStealth
VALUES
    ('Light Shield', 0);
INSERT INTO Armour
VALUES
    ('Ring Mail', 'Heavy Armour');
INSERT INTO Armour
VALUES
    ('Leather', 'Light Armour');
INSERT INTO Armour
VALUES
    ('Chain Mail', 'Heavy Armour');
INSERT INTO Armour
VALUES
    ('Studded Leather', 'Light Armour');
INSERT INTO Armour
VALUES
    ('Basic Shield', 'Light Shield');
INSERT INTO Tool
VALUES
    ('Jewelers Tools');
INSERT INTO Tool
VALUES
    ('Disguise Kit');
INSERT INTO Tool
VALUES
    ('Flute');
INSERT INTO Tool
VALUES
    ('Basic Alchemy Set');
INSERT INTO Tool
VALUES
    ('Painters Tools');
INSERT INTO Owns
VALUES
    ('Warhammer', 100002, 100001, 1);
INSERT INTO Owns
VALUES
    ('Warhammer', 100005, 100003, 1);
INSERT INTO Owns
VALUES
    ('Gold Piece', 100000, 100000, 142);
INSERT INTO Owns
VALUES
    ('Gold Piece', 100001, 100000, 6);
INSERT INTO Owns
VALUES
    ('Gold Piece', 100002, 100001, 12);
INSERT INTO Owns
VALUES
    ('Gold Piece', 100003, 100002, 533);
INSERT INTO Owns
VALUES
    ('Gold Piece', 100004, 100002, 543);
INSERT INTO Owns
VALUES
    ('Gold Piece', 100005, 100003, 96);
INSERT INTO Owns
VALUES
    ('Gold Piece', 100006, 100004, 457);
INSERT INTO Owns
VALUES
    ('Gold Piece', 100007, 100005, 1363);
INSERT INTO Owns
VALUES
    ('Gold Piece', 100008, 100006, 86);
INSERT INTO Owns
VALUES
    ('Gold Piece', 100009, 100005, 2);
INSERT INTO Owns
VALUES
    ('Longbow', 100003, 100002, 1);
INSERT INTO Owns
VALUES
    ('Ring Mail', 100006, 100004, 1);
INSERT INTO Owns
VALUES
    ('Leather', 100005, 100003, 1);
INSERT INTO Owns
VALUES
    ('Jewelers Tools', 100001, 100000, 1);
INSERT INTO Skill
VALUES
    ('Black Tentacles', 'Creepy black tentacles...', 'Spell', '90ft', 1);
INSERT INTO Skill
VALUES
    ('Confusion', 'Makes people confused!!', 'Spell', '10ft Sphere', 1);
INSERT INTO Skill
VALUES
    ('Heal', 'Heal chosen character', 'Spell', 'Single character', 1);
INSERT INTO Skill
VALUES
    ('Light', 'Create a ball of light to illuminate your path', 'Spell', '10ft', 1);
INSERT INTO Skill
VALUES
    ('Zone of Truth', 'Make people tell the truth within zone', 'Spell', '5ft Sphere', 1);
INSERT INTO Skill
VALUES
    ('Slashing Attack', 'Slices someone with a sharp blade.', 'Attack', '5ft', 1);
INSERT INTO Skill
VALUES
    ('Bludgeoning Attack', 'Hits someone with a dull, hard object.', 'Attack', '5ft', 1);
INSERT INTO Skill
VALUES
    ('Kick', 'Kick them.', 'Attack', '3ft', 1);
INSERT INTO Skill
VALUES
    ('Thrust', 'Lunge forward to pierce enemy', 'Attack', '15ft', 1);
INSERT INTO Skill
VALUES
    ('Punch', 'Hit someone with fists.', 'Attack', '2ft', 1);
INSERT INTO Skill
VALUES
    ('Disguised', 'This character is disguised.', 'Feature', null, 0);
INSERT INTO Attack
VALUES
    ('Slashing Attack', 8);
INSERT INTO Attack
VALUES
    ('Bludgeoning Attack', 5);
INSERT INTO Attack
VALUES
    ('Kick', 2);
INSERT INTO Attack
VALUES
    ('Thrust', 4);
INSERT INTO Attack
VALUES
    ('Punch', 1);
INSERT INTO Feature
VALUES
    ('Disguised');
INSERT INTO Spell
VALUES
    ('Black Tentacles', 15);
INSERT INTO Spell
VALUES
    ('Confusion', 10);
INSERT INTO Spell
VALUES
    ('Heal', 5);
INSERT INTO Spell
VALUES
    ('Light', 2);
INSERT INTO Spell
VALUES
    ('Zone of Truth', 10);
INSERT INTO Has
VALUES
    ('Black Tentacles', 100001, 100000, 1);
INSERT INTO Has
VALUES
    ('Black Tentacles', 100003, 100002, 1);
INSERT INTO Has
VALUES
    ('Confusion', 100005, 100003, 1);
INSERT INTO Has
VALUES
    ('Disguised', 100001, 100000, 1);
INSERT INTO Has
VALUES
    ('Bludgeoning Attack', 100008, 100006, 3);

COMMIT;