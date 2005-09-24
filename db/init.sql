-- This is an initilisation script for your databse. It's the good time to 
-- think about your dialplan and write it down

-- modules
insert into module values ( 1, 'people');
insert into module values ( 2, 'geo');
insert into module values ( 3, 'outside');
insert into module values ( 4, 'menu');
insert into module values ( 5, 'AsteriskGoto');

-- action 0 is always Dial
-- action <=100 are reserved
-- action > 100 could be use inside a module
insert into module_action values (1, 0, 'Dial', NULL);
insert into module_action values (2, 0, 'Dial', NULL);
insert into module_action values (3, 0, 'Dial', NULL);
insert into module_action values (4, 0, 'Dial', NULL);
insert into module_action values (5, 0, 'Dial', NULL);

-- number between 100 and 999 are geographical extension
insert into extension values ( '000', NULL, 2, '999'); 

-- number between 1000 and 9999 are people
insert into extension values ( '0000', NULL, 1, '9999');

-- number between 10000 and 9223372036854775807 are outside
insert into extension values ('00000', NULL, 3, '99999999999999999999');

-- one groupe with everyone
insert into grp values (0, 'Everyone');

-- one administrator (set your password here an integer value)
insert into People values ('0000', 'admin', 'admin', 'admin', '1234', true, 
				'asterisk@localhost');
insert into grp values (1, 'Administrators');
insert into grp_has_people values (1, '0000');
insert into grp_has_people values (0, '0000');

-- voip channels
insert into voipchannel values (1, 'SIP');
insert into voipchannel values (2, 'IAX2');

-- everyone could dial every module
insert into rights values (0, 0, 1);
insert into rights values (0, 0, 2);
insert into rights values (0, 0, 3);
insert into rights values (0, 0, 4);
insert into rights values (0, 0, 5);

-- sound sequence
insert into AgiSound values (1, 'notFound');
insert into AgiSound_Set values (1, 0, 1);
insert into AgiSound values (2, 'endOfMoney');
insert into AgiSound_Set values (2, 0, 2);
insert into AgiSound values (3, 'notEnoughMoney');
insert into AgiSound_Set values (3, 0, 3);
insert into AgiSound values (4, 'priceAnnounce');
insert into AgiSound_Set values (4, 0, 4);
insert into AgiSound values (5, 'unvailAtPrice');
insert into AgiSound_Set values (5, 0, 5);
insert into AgiSound values (6, 'currency');
insert into AgiSound_Set values (6, 0, 6);
