# Testing CSRF vulnerabilities (Issue #94)

How to test:

1. Start phppgadmin:

```
$ cd /path/to/phppgadmin
$ php -S localhost:8000
```

2. Set up a testing domain in /etc/hosts:

```
127.0.0.1       localhost2
```

3. Start the tests

```
$ cd /path/to/phppgadmin/tests/manual/issue-94
$ php -S localhost2:8001
```

4. Open both sites in the same browser (different windows or tabs):

```
http://localhost:8000
```

```
http://localhost2:8001
```

5. Log in to phppgadmin

6. Run a test

Choose a test from the list. Open your console, and click "Submit Request" -- you should see a CORS error, but the request should also appear in the network tab. Open it to see the response.

If you see a login page, phppgadmin is protected. If not, phppgadmin is vulnerable.

