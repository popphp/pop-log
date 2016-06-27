CREATE TABLE "[{table}]" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "timestamp" datetime,
  "level" integer,
  "name" varchar,
  "message" text,
  "context" text,
  UNIQUE ("id")
) ;

INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('[{table}]', 0);