#!/usr/bin/env bash

(cat request.json; sleep 1) | nc localhost 8080
