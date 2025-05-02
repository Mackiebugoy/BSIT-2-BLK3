import mysql.connector
from tkinter import messagebox

def db_connection():
    try:
        mydb = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="login_db"
        )
        return mydb
    except mysql.connector.Error as err:
        messagebox.showerror("Database Connection", f"Error: {err}")
        return None

def authenticate(email, password):
    mydb = db_connection()
    if mydb is None:
        return False
    cursor = mydb.cursor()
    query = "SELECT * FROM users WHERE email=%s AND password=%s"
    cursor.execute(query, (email, password))
    result = cursor.fetchone()
    cursor.close()
    mydb.close()
    return result is not None 