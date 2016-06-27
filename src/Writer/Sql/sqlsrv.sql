CREATE TABLE [[{table}]] (
  [id] int IDENTITY(1,1) PRIMARY KEY NOT NULL,
  [timestamp] timestamp,
  [level] int,
  [name] varchar,
  [message] text,
  [context] text,
  PRIMARY KEY ("id")
) ;
