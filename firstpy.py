
def factorial(n):
    acc = 1
    for i in range(1,n+1):
        acc *= i
    return acc
print(factorial(2))
print(factorial(5))
print(factorial(3))
print(factorial(1))