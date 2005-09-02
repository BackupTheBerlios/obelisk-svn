-- you have to create first extension_type

-- this is an implementation for pgsql

create domain extension_type as varchar(20) check (value ~ '^[0-9\*]*$');

create function extension_type_comp(extension_type, extension_type) returns integer as '
begin
if ( Length($1) = Length($2) ) then
        begin
		if $1 < $2 then
			return -1;
		else
			begin
				if $1 > $2 then return 1;
				else return 0;
				end if;
		end;
		end if;
	end;
else
	begin           
		if ( Length($1) > Length($2) ) THEN
			return 1;
		else
			return -1;
		end if;
	end;
end if;
end;'
LANGUAGE PLPGSQL;


