CREATE TABLE Network (
  ID INTEGER UNSIGNED NOT NULL,
  Name VARCHAR(64) NOT NULL,
  SubNetwork_function VARCHAR(24) NULL,
  PRIMARY KEY(ID)
);

CREATE TABLE NetworkProvider (
  ID INTEGER UNSIGNED NOT NULL,
  Name VARCHAR(32) NOT NULL,
  Channel VARCHAR(128) NOT NULL,
  DialOpts VARCHAR(10) NOT NULL,
  PRIMARY KEY(ID)
);

CREATE TABLE Grp (
  ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  Name VARCHAR(32) NOT NULL,
  PRIMARY KEY(ID)
);

CREATE TABLE Module (
  ID INTEGER UNSIGNED NOT NULL,
  Name VARCHAR(20) NOT NULL,
  PRIMARY KEY(ID)
);

CREATE TABLE NetworkTimeZone (
  ID INTEGER UNSIGNED NOT NULL,
  Name VARCHAR(32) NOT NULL,
  PRIMARY KEY(ID),
  UNIQUE INDEX NetworkTimeZone_Unique(Name)
);

CREATE TABLE VoIPChannel (
  ID SMALLINT UNSIGNED NOT NULL,
  ChanType VARCHAR(4) NOT NULL,
  PRIMARY KEY(ID),
  UNIQUE INDEX Account_Channel_UNIQ_CT(ChanType)
);

CREATE TABLE WebInterface (
  ID INTEGER UNSIGNED NOT NULL,
  Name VARCHAR(20) NOT NULL,
  ScreenName VARCHAR(40) NOT NULL,
  LogIn BOOL NOT NULL,
  PRIMARY KEY(ID)
);

CREATE TABLE People (
  Extension extension_type NOT NULL,
  username VARCHAR(32) NOT NULL,
  Name VARCHAR(48) NOT NULL,
  FirstName VARCHAR(48) NOT NULL,
  pwd INTEGER UNSIGNED NOT NULL,
  enable BOOL NOT NULL,
  mail VARCHAR(50) NOT NULL,
  PRIMARY KEY(Extension),
  UNIQUE INDEX People_usernameUniq(username)
);

CREATE TABLE Raw_Extension (
  Extension extension_type NOT NULL,
  price DECIMAL(5,4) NOT NULL,
  agi VARCHAR(512) NOT NULL,
  PRIMARY KEY(Extension)
);

CREATE TABLE AgiSound (
  ID INTEGER UNSIGNED NOT NULL,
  Filename VARCHAR(20) NOT NULL,
  PRIMARY KEY(ID)
);

CREATE TABLE AsteriskGoto (
  Extension extension_type NOT NULL,
  context VARCHAR(32) NOT NULL,
  PRIMARY KEY(Extension)
);

CREATE TABLE Geographical_Group (
  ID INTEGER UNSIGNED NOT NULL,
  Name VARCHAR(32) NOT NULL,
  PRIMARY KEY(ID)
);

CREATE TABLE NetworkTimeZone_Details (
  NetworkTimeZone_ID INTEGER UNSIGNED NOT NULL,
  ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  start_min SMALLINT UNSIGNED NOT NULL,
  start_hour SMALLINT UNSIGNED NOT NULL,
  start_dom SMALLINT UNSIGNED NOT NULL,
  start_month SMALLINT UNSIGNED NOT NULL,
  start_dow SMALLINT UNSIGNED NOT NULL,
  end_min SMALLINT UNSIGNED NOT NULL,
  end_hour SMALLINT UNSIGNED NOT NULL,
  end_dom SMALLINT UNSIGNED NOT NULL,
  end_month SMALLINT UNSIGNED NOT NULL,
  end_dow SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY(NetworkTimeZone_ID, ID),
  INDEX NetworkTimeZone_Details_FKIndex1(NetworkTimeZone_ID),
  FOREIGN KEY(NetworkTimeZone_ID)
    REFERENCES NetworkTimeZone(ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE
);

CREATE TABLE AgiSound_Set (
  ID INTEGER UNSIGNED NOT NULL,
  Priority INTEGER UNSIGNED NOT NULL,
  AgiSound_ID INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(ID, Priority),
  INDEX AgiSound_Set_FKIndex1(AgiSound_ID),
  FOREIGN KEY(AgiSound_ID)
    REFERENCES AgiSound(ID)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION
);

CREATE TABLE AgiLog (
  ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  Responsable_Extension extension_type NULL,
  LogWhen DATETIME NOT NULL DEFAULT 'now()',
  CallerId VARCHAR(64) NOT NULL,
  Extension extension_type NOT NULL,
  Price INTEGER UNSIGNED NOT NULL,
  Duration INTEGER UNSIGNED NOT NULL,
  CallStatus VARCHAR(15) NOT NULL,
  PRIMARY KEY(ID),
  INDEX AgiLog_FKIndex1(Responsable_Extension),
  FOREIGN KEY(Responsable_Extension)
    REFERENCES People(Extension)
      ON DELETE NO ACTION
      ON UPDATE CASCADE
);

CREATE TABLE People_PrePay_Settings (
  People_Extension extension_type NOT NULL,
  Credit DECIMAL(8,4) NOT NULL DEFAULT '0',
  Announce DECIMAL(1) NOT NULL DEFAULT '1',
  AskHigherCost BOOL NOT NULL,
  AllowOtherCID BOOL NOT NULL,
  PRIMARY KEY(People_Extension),
  INDEX People_PrePay_Settings_FKIndex1(People_Extension),
  FOREIGN KEY(People_Extension)
    REFERENCES People(Extension)
      ON DELETE CASCADE
      ON UPDATE CASCADE
);

CREATE TABLE Module_Action (
  Module_ID INTEGER UNSIGNED NOT NULL,
  Action_ID INTEGER UNSIGNED NOT NULL,
  Name VARCHAR(20) NULL,
  Description TEXT NULL,
  PRIMARY KEY(Module_ID, Action_ID),
  INDEX Module_Action_FKIndex1(Module_ID),
  FOREIGN KEY(Module_ID)
    REFERENCES Module(ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE
);

CREATE TABLE NetworkMask (
  ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  Network_ID INTEGER UNSIGNED NOT NULL,
  extStart extension_type NOT NULL,
  extEnd extension_type NOT NULL,
  PRIMARY KEY(ID, Network_ID),
  INDEX NetworkMask_FKIndex1(Network_ID),
  FOREIGN KEY(Network_ID)
    REFERENCES Network(ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE
);

CREATE TABLE VoIPAccount (
  People_Extension extension_type NOT NULL,
  ID INTEGER UNSIGNED NOT NULL,
  VoIPChannel_ID SMALLINT UNSIGNED NOT NULL,
  Enable BOOL NOT NULL,
  PRIMARY KEY(People_Extension, ID),
  INDEX VoIPAccount_FKIndex1(People_Extension),
  INDEX VoIPAccount_FKIndex2(VoIPChannel_ID),
  FOREIGN KEY(People_Extension)
    REFERENCES People(Extension)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(VoIPChannel_ID)
    REFERENCES VoIPChannel(ID)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION
);

CREATE TABLE AgiInterface (
  ID INTEGER UNSIGNED NOT NULL,
  AgiSound_Set_Priority INTEGER UNSIGNED NOT NULL,
  Sound_Outtro INTEGER UNSIGNED NOT NULL,
  Sound_Intro INTEGER UNSIGNED NOT NULL,
  Name VARCHAR(20) NOT NULL,
  LogIn BOOL NOT NULL,
  PRIMARY KEY(ID),
  INDEX AgiInterface_FKIndex1(Sound_Intro, AgiSound_Set_Priority),
  INDEX AgiInterface_FKIndex2(Sound_Outtro, AgiSound_Set_Priority),
  FOREIGN KEY(Sound_Intro, AgiSound_Set_Priority)
    REFERENCES AgiSound_Set(ID, Priority)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION,
  FOREIGN KEY(Sound_Outtro, AgiSound_Set_Priority)
    REFERENCES AgiSound_Set(ID, Priority)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION
);

CREATE TABLE Grp_has_People (
  Grp_ID INTEGER UNSIGNED NOT NULL,
  People_Extension extension_type NOT NULL,
  PRIMARY KEY(Grp_ID, People_Extension),
  INDEX Groups_has_Peoples_FKIndex1(Grp_ID),
  INDEX Grp_has_People_FKIndex2(People_Extension),
  FOREIGN KEY(Grp_ID)
    REFERENCES Grp(ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(People_Extension)
    REFERENCES People(Extension)
      ON DELETE CASCADE
      ON UPDATE CASCADE
);

CREATE TABLE Rights (
  Grp_ID INTEGER UNSIGNED NOT NULL,
  Module_Action_ID INTEGER UNSIGNED NOT NULL,
  Module_ID INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(Grp_ID, Module_Action_ID, Module_ID),
  INDEX Rights_FKIndex1(Grp_ID),
  INDEX Right_FKIndex2(Module_ID, Module_Action_ID),
  FOREIGN KEY(Grp_ID)
    REFERENCES Grp(ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(Module_ID, Module_Action_ID)
    REFERENCES Module_Action(Module_ID, Action_ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE
);

CREATE TABLE Geographical_alias (
  Extension extension_type NOT NULL,
  Geographical_Group_ID INTEGER UNSIGNED NOT NULL,
  People_Extension extension_type NOT NULL,
  PRIMARY KEY(Extension, Geographical_Group_ID),
  INDEX Geographical_alias_FKIndex1(Geographical_Group_ID),
  INDEX Geographical_alias_FKIndex2(People_Extension),
  UNIQUE INDEX Geographical_alias_uniqPepGrp(People_Extension),
  FOREIGN KEY(People_Extension)
    REFERENCES People(Extension)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(Geographical_Group_ID)
    REFERENCES Geographical_Group(ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE
);

CREATE TABLE Extension (
  Extension extension_type NOT NULL,
  Responsable_Extension extension_type NULL,
  Module_ID INTEGER UNSIGNED NOT NULL,
  ext_end extension_type NULL,
  PRIMARY KEY(Extension),
  INDEX extension_FKIndex1(Module_ID),
  INDEX Extension_FKIndex2(Responsable_Extension),
  FOREIGN KEY(Module_ID)
    REFERENCES Module(ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(Responsable_Extension)
    REFERENCES People(Extension)
      ON DELETE SET NULL
      ON UPDATE CASCADE
);

CREATE TABLE WebMenu (
  ID INTEGER UNSIGNED NOT NULL,
  Module_Action_ID INTEGER UNSIGNED NOT NULL,
  Module_ID INTEGER UNSIGNED NOT NULL,
  Parent INTEGER UNSIGNED NULL,
  WebInterface_ID INTEGER UNSIGNED NOT NULL,
  Name VARCHAR(20) NOT NULL,
  ScreenName VARCHAR(40) NOT NULL,
  Filename VARCHAR(20) NOT NULL,
  Action VARCHAR(10) NOT NULL,
  PRIMARY KEY(ID),
  INDEX WebMenu_FKIndex2(Module_ID, Module_Action_ID),
  INDEX WebMenu_FKIndex3(WebInterface_ID),
  INDEX WebMenu_FKIndex3(Parent),
  FOREIGN KEY(Module_ID, Module_Action_ID)
    REFERENCES Module_Action(Module_ID, Action_ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(WebInterface_ID)
    REFERENCES WebInterface(ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(Parent)
    REFERENCES WebMenu(ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE
);

CREATE TABLE Price (
  Network_ID INTEGER UNSIGNED NOT NULL,
  NetworkProvider_ID INTEGER UNSIGNED NOT NULL,
  NetworkTimeZone_ID INTEGER UNSIGNED NOT NULL,
  Price DECIMAL(5,4) NOT NULL,
  ConnectionPrice DECIMAL(5,4) NOT NULL,
  AddDigits extension_type NOT NULL,
  RmDigits SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY(Network_ID, NetworkProvider_ID, NetworkTimeZone_ID),
  INDEX Price_FKIndex1(Network_ID),
  INDEX Price_FKIndex2(NetworkProvider_ID),
  INDEX Price_FKIndex3(NetworkTimeZone_ID),
  FOREIGN KEY(Network_ID)
    REFERENCES Network(ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(NetworkProvider_ID)
    REFERENCES NetworkProvider(ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(NetworkTimeZone_ID)
    REFERENCES NetworkTimeZone(ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE
);

CREATE TABLE AgiMenu (
  ID INTEGER UNSIGNED NOT NULL,
  AgiSound_Set_Priority INTEGER UNSIGNED NOT NULL,
  Sound_After INTEGER UNSIGNED NULL,
  Sound_Announce INTEGER UNSIGNED NULL,
  Sound_Before INTEGER UNSIGNED NULL,
  Module_Action_ID INTEGER UNSIGNED NULL,
  Module_ID INTEGER UNSIGNED NULL,
  Parent INTEGER UNSIGNED NULL,
  AgiInterface_ID INTEGER UNSIGNED NOT NULL,
  Name VARCHAR(20) NOT NULL,
  Filename VARCHAR(20) NOT NULL,
  Action VARCHAR(10) NOT NULL,
  AccessMask VARCHAR(10) NOT NULL,
  PRIMARY KEY(ID),
  INDEX AgiMenu_FKIndex2(Parent),
  INDEX AgiMenu_FKIndex2(AgiInterface_ID),
  INDEX AgiMenu_FKIndex3(Module_ID, Module_Action_ID),
  INDEX AgiMenu_FKIndex4(Sound_Announce, AgiSound_Set_Priority),
  INDEX AgiMenu_FKIndex5(Sound_Before, AgiSound_Set_Priority),
  INDEX AgiMenu_FKIndex6(Sound_After, AgiSound_Set_Priority),
  FOREIGN KEY(Parent)
    REFERENCES AgiMenu(ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(AgiInterface_ID)
    REFERENCES AgiInterface(ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(Module_ID, Module_Action_ID)
    REFERENCES Module_Action(Module_ID, Action_ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(Sound_Announce, AgiSound_Set_Priority)
    REFERENCES AgiSound_Set(ID, Priority)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION,
  FOREIGN KEY(Sound_Before, AgiSound_Set_Priority)
    REFERENCES AgiSound_Set(ID, Priority)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION,
  FOREIGN KEY(Sound_After, AgiSound_Set_Priority)
    REFERENCES AgiSound_Set(ID, Priority)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION
);

CREATE TABLE Sip (
  VoIPAccount_ID INTEGER UNSIGNED NOT NULL,
  VoIPAccount_People_Extension extension_type NOT NULL,
  canreinvite BOOL NOT NULL,
  host VARCHAR(50) NULL,
  port SMALLINT UNSIGNED NULL,
  dtmfmode SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY(VoIPAccount_ID, VoIPAccount_People_Extension),
  INDEX Sip_FKIndex1(VoIPAccount_People_Extension, VoIPAccount_ID),
  FOREIGN KEY(VoIPAccount_People_Extension, VoIPAccount_ID)
    REFERENCES VoIPAccount(People_Extension, ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE
);

CREATE TABLE Iax (
  VoIPAccount_ID INTEGER UNSIGNED NOT NULL,
  VoIPAccount_People_Extension extension_type NOT NULL,
  notransfer BOOL NOT NULL,
  host VARCHAR(50) NULL,
  port SMALLINT UNSIGNED NULL,
  PRIMARY KEY(VoIPAccount_ID, VoIPAccount_People_Extension),
  INDEX Iax_FKIndex1(VoIPAccount_People_Extension, VoIPAccount_ID),
  FOREIGN KEY(VoIPAccount_People_Extension, VoIPAccount_ID)
    REFERENCES VoIPAccount(People_Extension, ID)
      ON DELETE CASCADE
      ON UPDATE CASCADE
);


