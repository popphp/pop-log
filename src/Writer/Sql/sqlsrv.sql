CREATE TABLE [[{table}]] (
  [id] int IDENTITY(1,1) PRIMARY KEY NOT NULL,
  [timestamp] timestamp,
  [priority] int,
  [name] varchar,
  [message] text,
  PRIMARY KEY ("id")
) ;
