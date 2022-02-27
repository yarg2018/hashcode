import sys
from scipy.sparse import csr_matrix
import pandas as pd
import numpy as np


level = 'PRO'


def log(message, lev):
    if lev == 'DEV' and level == 'DEV':
        print("[*] {}".format(message))
    elif lev == 'PRO' and level == 'PRO':
        print("{}".format(message))


def main():
    input_files = [
        'a_an_example',
        'b_basic',
        'c_coarse'#,
        #'d_difficult',
        #'e_elaborate'
    ]
    for i_f in input_files:
        log("Working on file {}".format(i_f), 'DEV')
        clients = []
        full_items = []
        with open("input_data/{}.in.txt".format(i_f), 'r') as f:
            lines = f.readlines()
            f.close()
        if len(lines) > 0:
            log('---------------------------------', 'PRO')
            log(f"Digesting input {i_f}", 'PRO')
            potential_clients = int(lines.pop(0))
            max_ingredients_likes_no = 0
            max_ingredients_dislikes_no = 0
            data_like = []
            data_dislike = []
            client_like = []
            client_dislike = []
            column_like = []
            column_dislike = []
            gusti = {}
            k_dislike = 0
            for i in range(potential_clients):
                item_likes, item_dislikes = lines.pop(0).split(), lines.pop(0).split()
                no_ingredients_likes = int(item_likes.pop(0))
                for i_likes in range(no_ingredients_likes):
                    data_like.append(1/no_ingredients_likes)
                    client_like.append(i)
                    column_like.append(gusti.setdefault(item_likes[i_likes], len(gusti)))

                no_ingredients_dislikes = int(item_dislikes.pop(0))
                for i_dislikes in range(no_ingredients_dislikes):
                    data_dislike.append(-1)
                    client_dislike.append(i)
                    column_dislike.append(gusti.setdefault(item_dislikes[i_dislikes], len(gusti)))

                max_ingredients_likes_no = max([no_ingredients_likes, max_ingredients_likes_no])
                max_ingredients_dislikes_no = max([no_ingredients_dislikes, max_ingredients_dislikes_no])

                clients.append({
                    'client_no': i,
                    'no_ingredients_likes': no_ingredients_likes,
                    'ingredients_likes': item_likes if len(item_likes) > 0 else '',
                    'no_ingredients_dislikes': no_ingredients_dislikes,
                    'ingredients_dislikes': item_dislikes if len(item_dislikes) > 0 else '',
                    'priority': no_ingredients_likes + no_ingredients_dislikes*2,
                })
            log("File {} has {} potential clients".format(i_f, potential_clients), 'DEV')

            # clients = sorted(clients, key=lambda d: d['priority'], reverse=True)

            log(f"Maximum no of ingredients (like): {max_ingredients_likes_no}", "PRO")
            log(f"Maximum no of ingredients (dislike): {max_ingredients_dislikes_no}", "PRO")

            prod_clients_ingredients_likes = max_ingredients_likes_no * potential_clients
            prod_clients_ingredients_dislikes = max_ingredients_dislikes_no * potential_clients

            log(f"Product Clients * Ingredients Likes: {prod_clients_ingredients_likes}", 'PRO')
            log(f"Product Clients * Ingredients Dislikes: {prod_clients_ingredients_dislikes}", 'PRO')

            print(column_like)
            print(gusti)
            colonne = [k for k, v in gusti.items() if v <= max(column_like)]
            #print(colonne)
            matrix_like = csr_matrix((data_like, (client_like, column_like)))
            df_like = pd.DataFrame.sparse.from_spmatrix(matrix_like, columns=colonne)
            print(df_like.head())


            print(column_dislike)
            if(len(column_dislike) == 0):
                df_dislike = pd.DataFrame()
            else:
                colonne = [k for k, v in gusti.items() if v <= max(column_dislike)]
                matrix_dislike = csr_matrix((data_dislike, (client_dislike, column_dislike)))
                df_dislike = pd.DataFrame.sparse.from_spmatrix(matrix_dislike, columns=colonne)
                print(df_dislike.head())

            print((df_like.add(df_dislike)))

            item_likes = []
            item_dislikes = []
            for c in clients:
                log(c, 'DEV')
                ###################################################################################
                # increase score on case E
                if i_f.startswith('e_'):
                    if c['no_ingredients_dislikes'] > 0:
                        # log('Skipping because product clients * ingredients within range 2k-3k', 'PRO')
                        continue
                ###################################################################################
                # increase score on case D
                if i_f.startswith('d_'):
                    if c['no_ingredients_dislikes'] > 0:
                        continue
                ###################################################################################
                item_likes += c['ingredients_likes']
                item_dislikes += c['ingredients_dislikes']
            likes = [x for x in item_likes if x not in item_dislikes]
            likes = set(likes)
            log(likes, 'DEV')
            log(f"Length chosen pizza's ingredients: {len(likes)}", 'PRO')
            output_string = "{} {}".format(len(likes), ' '.join(likes))
            with open("output/{}.out.txt".format(i_f), 'w') as of:
                of.write(output_string)
                of.close()
            log('---------------------------------', 'PRO')

    sys.exit(0)


# Press the green button in the gutter to run the script.
if __name__ == '__main__':
    main()

# See PyCharm help at https://www.jetbrains.com/help/pycharm/


