CREATE TABLE "[{table}]" (
  "id" NUMBER GENERATED ALWAYS AS IDENTITY,
  "timestamp" timestamp,
  "level" int,
  "name" varchar,
  "message" text,
  "context" text,
  PRIMARY KEY ("id")
) ;
