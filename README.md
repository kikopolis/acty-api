Hi! This is my submission as test work.

Apologies for long time, but I honestly read the spec wrong and spent some time chasing the rabbit. Then I had troubles with my Docker and WSL and then Postman
did not want to play with PHP and all manners of terribleness.

I am fairly inexperienced in pure SQL, so I wrote a different solution than I originally had planned. I did try, but the sorting, grouping and all manners of
black magic queries are beyond my skill and the timeframe reasonable for such a task.

In today's ecosystem, one is spoiled for choice in use of NoSQL and ORM's and with the reasons already mentioned, I chose Doctrine.

The endpoint is the same, simply send requests to http://localhost with methods and explanations of what to send below.

Also, no error handling and no dependency injection is used.

#### Testing

- GET : sample is in app folder as GET.json. Send a json object with name and page parameters. Page is optional and will default to 1.
- POST : sample is in app folder as POST.json. Taken directly from the specs.