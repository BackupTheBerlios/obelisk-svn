-- This is an initilisation script for your databse. It's the good time to 
-- think about your dialplan and write it down

-- modules
insert into module values ( 1, 'people');
insert into module values ( 2, 'geo');
insert into module values ( 3, 'outside');
insert into module values ( 4, 'menu');

-- number between 100 and 999 are geographical extension
insert into extension values ( 100, NULL, 2, 999); 

-- number between 1000 and 9999 are people
insert into extension values ( 1000, NULL, 1, 9999);

-- number between 10000 and 9223372036854775807 are outside
insert into extension values (10000, NULL, 3, 9223372036854775807);

-- one administrator (set your password here an integer value)
insert into People values (0, 'admin', 'admin', 'admin', '1234', true, 
				'asterisk@localhost');
insert into grp values (0, 'Administrators');
insert into grp_has_people values (0, 0);

-- voip channels
insert into voipchannel values (1, 'SIP');
insert into voipchannel values (2, 'IAX2');

